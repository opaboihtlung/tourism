<?php

namespace App\Http\Controllers\Api;

use Throwable;
use App\Models\User;
use App\Models\Lodge;
use Carbon\CarbonPeriod;
use App\Models\BookedDates;
use App\Models\UserBooking;
use Illuminate\Http\Request;
use App\Models\LodgeRoomData;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\UserBooking as ModelsUserBooking;
use Illuminate\Support\Carbon;

class BookingsController extends Controller
{
  public function getMyBookings(Request $request)
  {
    $bookings = UserBooking::where('users_id', $request->user_id)->whereIn('status', ['Confirmed', 'Cancelled'])->with(['lodgeRoomData'])->get();
    return response()->json(['bookings' => $bookings]);
  }

  public function checkDate(Request $request)
  {
    $roomsAvailable = LodgeRoomData::where('lodges_id', $request->lodge_id)->where('lodge_room_types_id', $request->room_type_id)->first();

    $period = CarbonPeriod::create($request->checkIn, date('Y-m-d', strtotime('-1 day', strtotime($request->checkOut))));
    $dates = [];

    foreach ($period as $date) {
      array_push($dates, $date->format('Y-m-d'));
    }

    $bookedDates = BookedDates::whereIn('booked', $dates)
      ->get()
      ->pluck('user_booking_id');

    $bookedRooms = UserBooking::whereIn('id', $bookedDates->unique())->where('status', 'Confirmed')->where('param1', '!=', 'Out')->where('lodge_room_data_id', $request->lodge_room_data_id)->sum('number_of_room_require');

    $noOfRoomsAvailable = ($roomsAvailable->room_available - $bookedRooms <= 0 ? 0 : $roomsAvailable->room_available - $bookedRooms);

    return response()->json(['no_of_rooms_available' => $noOfRoomsAvailable]);
  }

  public function cancelBooking(Request $request)
  {
    $templateId = '1407164844802974092';
    $templateId_lodge = '1407170669727078645';

    $booking = UserBooking::findOrFail($request->booking_id);

    $checkInDate = Carbon::parse($booking->check_in);
    $checkInLimit = $checkInDate->addHours(12);

    if (now() < $checkInLimit) {
      $booking->cancelled_at = now();
      $booking->status = 'Cancelled';
      $booking->save();

      $user = User::findOrFail($booking->users_id);

      // Cancellation to user - mobile app
      try {
        Http::withHeaders([
          'Authorization' => "Bearer 604|Bjz4PiBqDeltyuRy7X6Zo2NR7lJwUZXQ16zSYWtD",
        ])->get("https://sms.msegs.in/api/send-sms", [
          'template_id' => $templateId,
          'message' => "Dear $user->name , your Booking has been cancelled . please login to https://mizoramtourism.com for details",
          'recipient' => $user->phone
        ]);
        Log::info("$request->booking_id, Cancellation Mobile");
      } catch (Throwable $ex) {
        Log::info("MZERROR:line-->BookingsController cancelBooking():::" . $ex);
      }

      //cancellation sent to lodge - mobile app
      try {
        $booking = ModelsUserBooking::findOrFail($request->booking_id);
        $lodge_room = LodgeRoomData::where('id', $booking->lodge_room_data_id)->first();
        $lodge = Lodge::where('id', $lodge_room->lodges_id)->first();

        Http::withHeaders([
          'Authorization' => "Bearer 604|Bjz4PiBqDeltyuRy7X6Zo2NR7lJwUZXQ16zSYWtD",
        ])->get("https://sms.msegs.in/api/send-sms", [
          'template_id' => $templateId_lodge,
          'message' => "$lodge->name, Booking made by $user->name with order id: $booking->order_id has been cancelled. Please login to https://mizoramtourism.com for details. MZTOUR",
          'recipient' => $lodge->phone,
        ]);
      } catch (Throwable $ex) {
        Log::info("MZERROR:line-->BookingsController cancelBooking():::" . $ex);
      }

      return response()->json(['success' => 'Booking has been cancelled']);
    } else {
      return response()->json(['error' => 'Booking cancellation Timeout'], 400);
    }
  }
}
