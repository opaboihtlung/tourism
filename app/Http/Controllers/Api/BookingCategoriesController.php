<?php

namespace App\Http\Controllers\Api;

use PDF;
use DateTime;
use Throwable;
use App\Models\User;
use App\Models\Guest;
use App\Models\Lodge;
use GuzzleHttp\Client;
use App\Models\Payment;
use Carbon\CarbonPeriod;
use App\Models\UserLodge;
use App\Models\BookedDates;
use App\Models\UserBooking;
use Illuminate\Http\Request;
use App\Models\LodgeRoomData;
use App\Models\LodgeRoomType;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;

class BookingCategoriesController extends Controller
{
  public function getLodgeCreatedBooking(Request $request) {}

  public function userCreatedBookings(Request $request) {}

  public function getFailedPaymentBookings(Request $request)
  {
    $user = User::with('lodges')->find($request->user_id);
    switch ($user->role) {
      case 'lodge':
        if (count($user->lodges) != 0) {
          $lodgeID = $user->lodges->first()->id;
          $lodgeRoomData = LodgeRoomData::where('lodges_id', $lodgeID)->pluck('id');
          switch ($request->type) {
            case 'order_id':
              $bookings = UserBooking::where('status', 1)
                ->whereIn('lodge_room_data_id', $lodgeRoomData)
                ->where('order_id', 'LIKE', '%' . $request->key . '%')
                ->with(['users', 'lodgeRoomData', 'lodgeRoomData.lodges', 'lodgeRoomData.districtMetaData', 'lodgeRoomData.lodgeRoomTypes'])
                ->orderBy('created_at', 'DESC')
                ->paginate(10);
            case 'name':
            case 'phone':
              $citizen = User::where($request->type, 'LIKE', '$' . $request->key . '%')->get()->pluck('id');
              $bookings = UserBooking::where('status', 1)
                ->whereIn('users_id', $citizen)
                ->whereIn('lodge_room_data_id', $lodgeRoomData)
                ->where('order_id', 'LIKE', '%' . $request->key . '%')
                ->with(['users', 'lodgeRoomData', 'lodgeRoomData.lodges', 'lodgeRoomData.districtMetaData', 'lodgeRoomData.lodgeRoomTypes'])
                ->orderBy('created_at', 'DESC')
                ->paginate(10);
              break;
            default:
              $bookings = UserBooking::where('status', 1)
                ->whereIn('lodge_room_data_id', $lodgeRoomData)
                ->with(['users', 'lodgeRoomData', 'lodgeRoomData.lodges', 'lodgeRoomData.districtMetaData', 'lodgeRoomData.lodgeRoomTypes'])
                ->orderBy('created_at', 'DESC')
                ->paginate(10);
          }
        } else {
          $bookings = null;
        }
        break;
      default:
        switch ($request->type) {
          case 'order_id':
            $bookings = UserBooking::where('status', 1)
              ->where('order_id', $request->key)
              ->with(['users', 'lodgeRoomData', 'lodgeRoomData.lodges', 'lodgeRoomData.districtMetaData', 'lodgeRoomData.lodgeRoomTypes'])
              ->orderBy('created_at', 'DESC')
              ->paginate(10);
            break;
          case 'name':
          case 'phone':
            $citizen = User::where($request->type, 'LIKE', '%' . $request->key . '%')->pluck('id');
            $bookings = UserBooking::whereIn('users_id', $citizen)
              ->where('status', 1)
              ->with(['users', 'lodgeRoomData', 'lodgeRoomData.lodges', 'lodgeRoomData.districtMetaData', 'lodgeRoomData.lodgeRoomTypes'])
              ->orderBy('created_at', 'DESC')
              ->paginate(10);
            break;
          default:
            $bookings = UserBooking::where('status', 1)
              ->with(['users', 'lodgeRoomData', 'lodgeRoomData.lodges', 'lodgeRoomData.districtMetaData', 'lodgeRoomData.lodgeRoomTypes'])
              ->orderBy('created_at', 'DESC')
              ->paginate(10);
        }
    }
    return $bookings;
  }

  public function getFailedBookings(Request $request)
  {
    //Get bookings that failed or that was stop mid way during booking process
    //This function is made to recheck missing bookings i.e bookings that have successful transaction but whose status have not been updated
    switch ($request->type) {
      case 'order_id':
        $bookings = UserBooking::where('status', 'Booking Retracted')
          ->where('order_id', $request->key)
          ->with(['users', 'lodgeRoomData', 'lodgeRoomData.lodges', 'lodgeRoomData.districtMetaData', 'lodgeRoomData.lodgeRoomTypes'])
          ->orderBy('created_at', 'DESC')
          ->paginate(10);
        break;
      default:
        $bookings = UserBooking::where('status', 'Booking Retracted')
          ->with(['users', 'lodgeRoomData', 'lodgeRoomData.lodges', 'lodgeRoomData.districtMetaData', 'lodgeRoomData.lodgeRoomTypes'])
          ->orderBy('created_at', 'DESC')
          ->paginate(10);
    }
    return $bookings;
  }

  public function resendConfirmation(Request $request)
  {
    $templateId_user = '1407164844802974092';
    $templateId_lodge = '1407170833222683069';

    $booking = UserBooking::where('order_id', $request->orderId)->first();
    $booking->status = "Confirmed";
    $booking->param1 = "Booked";
    $booking->save();

    $payment = new Payment();
    $payment->transactionId = $request->transactionId;
    $payment->orderId = $request->orderId;
    $payment->currency = $request->currency;
    $payment->status = $request->status;
    $payment->amount = $request->amount;
    $payment->save();

    $period = CarbonPeriod::create($booking->check_in, date('Y-m-d', strtotime('-1 day', strtotime($booking->check_out))));

    foreach ($period as $date) {
      $bookedDate = new BookedDates;
      $bookedDate->user_booking_id = $booking->id;
      $bookedDate->lodge_room_data_id = $booking->lodge_room_data_id;
      $bookedDate->booked = $date->format('Y-m-d');
      $bookedDate->save();
    }

    $user = User::findOrFail($booking->users_id);

    //sms booking confirmation alert for lodges
    try {
      $booking = UserBooking::findOrFail($booking->id);
      $lodge_room = LodgeRoomData::where('id', $booking->lodge_room_data_id)->first();
      $lodge = Lodge::where('id', $lodge_room->lodges_id)->first();

      Http::withHeaders([
        'Authorization' => "Bearer 604|Bjz4PiBqDeltyuRy7X6Zo2NR7lJwUZXQ16zSYWtD",
      ])->get("https://sms.msegs.in/api/send-sms", [
        'template_id' => $templateId_lodge,
        'message' => "$lodge->name, Booking made by $user->name with order id: $booking->order_id has been confirmed. Please login to https://mizoramtourism.com for details. -MZTOUR",
        'recipient' => $lodge->phone,
      ]);
    } catch (Throwable $ex) {
      Log::info("MZERROR:line-smsConfirmationLodge->BookingCategoriesController resendConfirmation():::" . $ex);
    }

    //sms booking confirmation alert for users
    try {
      Http::withHeaders([
        'Authorization' => "Bearer 604|Bjz4PiBqDeltyuRy7X6Zo2NR7lJwUZXQ16zSYWtD",
      ])->get("https://sms.msegs.in/api/send-sms", [
        'template_id' => $templateId_user,
        'message' => "Dear $user->name , your Booking has been confirmed . please login to https://mizoramtourism.com for details",
        'recipient' => $user->phone
      ]);
    } catch (Throwable $ex) {
      Log::info("MZERROR:line-smsConfirmationUser->BookingCategoriesController resendConfirmation()::: " . $ex);
    }

    $room = LodgeRoomData::where('id', $booking->lodge_room_data_id)->with('lodgeRoomTypes')->first();
    $lodge = Lodge::where('id', $room->lodges_id)->first();

    try {
      $pdf = $this->attachPdf($request->orderId);
      Mail::send(
        'emails.confirmation-mail',
        ['name' => $user->name, 'booking' => $booking, 'room' => $room, 'lodge' => $lodge],
        function ($message) use ($user, $pdf, $booking) {
          $message->to($user->email)
            ->subject('Booking Confirmation')
            ->attachData($pdf->output(), $booking->order_id . '.pdf');
          $message->from('noreply@gmail.com', 'Booking confirmation');
        }
      );
    } catch (Throwable $ex) {
      Log::info("MZERROR:line-emailConfirmationUser->BookingCategoriesController resendConfirmation():::" . $ex);
    }

    $this->sendEmail($booking->order_id);

    return response()->json(['success' => 'Email resend successful']);
  }

  public function sendEmail($orderId)
  {
    try {
      $id = $orderId;
      $booking = UserBooking::where('order_id', $id)->first();
      $user = User::findOrFail($booking->users_id);
      $room = LodgeRoomData::where('id', $booking->lodge_room_data_id)->with('lodgeRoomTypes')->first();
      $lodge = Lodge::where('id', $room->lodges_id)->with('users')->first();
      Mail::send(
        'emails.new-booking',
        ['user' => $user, 'booking' => $booking, 'room' => $room],
        function ($message) use ($lodge) {
          $message->to($lodge->users->first()->email)
            ->subject('Booking Information');
          $message->from('noreply@gmail.com', 'Booking confirmation');
        }
      );
    } catch (Throwable $ex) {
      Log::info("MZERROR:line-emailConfirmationLodge->BookingCategoriesController sendEmail():::" . $ex);
    }
  }

  public function attachPdf($id)
  {
    $data = UserBooking::where('order_id', $id)->first()->toArray();
    $lodgeRoom = LodgeRoomData::where('id', $data['lodge_room_data_id'])->first()->toArray();
    $lodgeRoomType = LodgeRoomType::where('id', $lodgeRoom['lodge_room_types_id'])->first()->toArray();
    $lodge = Lodge::where('id', $lodgeRoom['lodges_id'])->with('districtMetaData')->first()->toArray();
    $guests = Guest::where('booking_id', $data['id'])->get();
    $date1 = new DateTime($data['check_in']);
    $date2 = new DateTime($data['check_out']);
    $interval = $date1->diff($date2);
    $noOfNights = (int) $interval->format('%a');
    $username = User::where('id', $data['users_id'])->first()->name; //added username to attach in pdf
    $pdf = PDF::loadView('pdf.user-invoice', compact('data', 'lodgeRoom', 'lodge', 'lodgeRoomType', 'guests', 'noOfNights', 'username'));
    return $pdf;
  }

  public function initiateRefund(Request $request)
  {
    $payment = Payment::where('orderId', $request->orderId)->first();
    if ($payment != null) {
      $payment->status = $request->status;
      $payment->save();
    }
    return 'refunded';
  }

  public function updateFailedTransaction(Request $request)
  {
    $userBookings = UserBooking::where('order_id', $request->orderId)->get();
    foreach ($userBookings as $booking) {
      $booking->status = 'TXN_FAILURE';
      $booking->save();
    }
    return 'saved';
  }

  public function checkRefund(Request $request)
  {
    $payment = Payment::where('orderid', $request->orderId)->first();
    return $payment;
  }

  public function cancelBooking($id)
  {
    $templateId_lodge = '1407170669727078645';
    $templateId = '1407164844802974092';
    $booking = UserBooking::findOrFail($id);
    $booking->status = 'Cancelled';
    $booking->cancelled_at = now();
    $booking->save();

    $user = User::findOrFail($booking->users_id);

    //sms cancellation alert for lodges
    try {
      $booking = UserBooking::findOrFail($booking->id);
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
      Log::info("MZERROR:line-278->BookingCategoriesController cancelBooking():::" . $ex);
    }

    //sms cancellation alert for users
    try {

      Http::withHeaders([
        'Authorization' => "Bearer 604|Bjz4PiBqDeltyuRy7X6Zo2NR7lJwUZXQ16zSYWtD",
      ])->get("https://sms.msegs.in/api/send-sms", [
        'template_id' => $templateId,
        'message' => "Dear $user->name , your Booking has been cancelled . please login to https://mizoramtourism.com for details",
        'recipient' => $user->phone
      ]);
    } catch (Throwable $ex) {
      Log::info("MZERROR:line-292->BookingCategoriesController cancelBooking():::" . $ex);
    }

    return 'Booking Cancelled';
  }

  public function getTotalGuests(Request $request)
  {
    $user = User::findOrFail($request->user_id);
    if ($user->role == 'admin') {
      $bookedDates = BookedDates::whereMonth('booked', today()->month)->groupBy('user_booking_id')->pluck('user_booking_id');
      $bookings = UserBooking::whereIn('id', $bookedDates)->where('status', 'Confirmed')->count();
    } else {
      $bookedDates = BookedDates::whereMonth('booked', today()->month)->groupBy('user_booking_id')->pluck('user_booking_id');
      $userLodge = UserLodge::where('users_id', $request->user_id)->first();
      $lodgeRoomData = LodgeRoomData::where('lodges_id', $userLodge->lodges_id)->pluck('id');
      $bookings = UserBooking::whereIn('id', $bookedDates)->whereIn('lodge_room_data_id', $lodgeRoomData)->where('status', 'Confirmed')->count();
    }
    return $bookings;
  }

  public function getTransactions(Request $request)
  {
    $dateString = '1-' . $request->month . '-' . $request->year;
    $month = Date('m', strtotime($dateString));
    $year = Date('Y', strtotime($dateString));

    $lodgeRooms = LodgeRoomData::where('lodges_id', $request->lodge_id)->get()->pluck('id');
    $userBookings = UserBooking::whereIn('lodge_room_data_id', $lodgeRooms)->pluck('order_id');
    $payments = Payment::whereIn('orderId', $userBookings)->whereMonth('created_at', $month)->whereYear('created_at', $year)->with('userBooking')->get()->groupBy('orderId');
    return $payments;
  }
}
