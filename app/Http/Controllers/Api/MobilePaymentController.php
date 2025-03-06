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
use App\Models\BookedDates;
use App\Models\UserBooking;
use Illuminate\Http\Request;
use App\Models\LodgeRoomData;
use App\Models\LodgeRoomType;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;

class MobilePaymentController extends Controller
{
  public function makePayment(Request $request)
  {
    try {
      $orderId = 'MZTOUR' . now()->timestamp;
      $guests = json_decode($request->guests, true);

      $userId = $request->userId;

      $booking = new UserBooking;
      $lodgeRoomData = LodgeRoomData::where('lodges_id', $request->lodgeId)->where('lodge_room_types_id', $request->roomType)->first();
      $booking->lodge_room_data_id = $lodgeRoomData->id;
      $booking->users_id = $userId;
      $booking->order_id = $orderId;
      $booking->number_of_room_require = $request->noOfRooms;
      $booking->check_in = date('Y-m-d', strtotime($request->checkIn));
      $booking->check_out = date('Y-m-d', strtotime($request->checkOut));
      $booking->status = 1;
      $booking->save();

      if (count($guests) != 0) {
        foreach ($guests as $guest) {
          $guest = json_decode($guest, true);
          $d = new Guest;
          $d->users_id = $userId;
          $d->first_name = $guest['name'];
          $d->email = $guest['email'];
          $d->phone = $guest['phone'];
          $d->suggestion = $request->suggestion;
          $d->booking_id = $booking->id;
          $d->save();
        }
      }
      //Bookings
      $lodge = Lodge::findOrFail($request->lodgeId);
      $customer = 'MZTOUR: ' . $lodge->name;
      $customer = json_encode($customer);
      $client = new Client();
      $response = $client->request('POST', 'https://paymentgw.mizoram.gov.in/api/initiate-mobile-payment', [
        'form_params' => [
          'callback_url' => 'https://mizoramtourism.com/api/save-bookings',
          'order_id' => $orderId,
          'amount' => $request->amount,
          'department_id' => 3,
          'customer' => $customer,
          'shouldSplit' => true,
          'splitMethod' => 'PERCENTAGE',
          'splitMid' => 'ZUnFVOlw678076036222',
          'splitPercentage' => '100',
        ]
      ]);
      $response = json_decode($response->getBody());

      return response()->json($response);
    } catch (\Throwable $th) {
      info($th);
      //throw $th;
    }
  }

  public function responseHandler(Request $request)
  {
    $templateId = '1407164844802974092';
    $templateId_lodge = '1407170833222683069';

    $payment = new Payment;
    $payment->transactionId = $request->transactionId;
    $payment->orderId = $request->orderId;
    $payment->currency = $request->currency;
    $payment->status = $request->status;
    $payment->amount = $request->amount;
    $payment->save();

    $booking = UserBooking::where('order_id', $payment->orderId)->first();
    $booking->status = $request->status == 'TXN_SUCCESS' ? 'Confirmed' : 'Payment Failure';
    $booking->param1 = 'Booked';
    $booking->save();

    if ($request->status == 'TXN_SUCCESS') {
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

      // sms booking confirmation alert for user
      try {
        Http::withHeaders([
          'Authorization' => "Bearer 604|Bjz4PiBqDeltyuRy7X6Zo2NR7lJwUZXQ16zSYWtD",
        ])->get("https://sms.msegs.in/api/send-sms", [
          'template_id' => $templateId,
          'message' => "Dear $user->name , your Booking has been confirmed . please login to https://mizoramtourism.com for details",
          'recipient' => $user->phone
        ]);
      } catch (Throwable $ex) {
        Log::info("MZERROR:line-119->MobilePaymentController responseHandler():::" . $ex);
      }

      $room = LodgeRoomData::where('id', $booking->lodge_room_data_id)->with('lodgeRoomTypes')->first();
      $lodge = Lodge::where('id', $room->lodges_id)->first();

      try {
        $userBooking = UserBooking::where('order_id', $request->orderId)->first()->toArray();
        $lodgeRoomData = LodgeRoomData::where('id', $userBooking['lodge_room_data_id'])->first()->toArray();
        $lodgeRoomType = LodgeRoomType::where('id', $lodgeRoomData['lodge_room_types_id'])->first()->toArray();
        $lodgeBooked = Lodge::where('id', $lodgeRoomData['lodges_id'])->with('districtMetaData')->first()->toArray();
        $guests = Guest::where('booking_id', $userBooking['id'])->get();
        $date1 = new DateTime($userBooking['check_in']);
        $date2 = new DateTime($userBooking['check_out']);
        $interval = $date1->diff($date2);
        $noOfNights = (int) $interval->format('%a');
        $pdf = $this->attachPdf($userBooking, $lodgeRoomData, $lodgeBooked, $lodgeRoomType, $guests, $noOfNights, $user->name);
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
        Log::info("MZERROR:line-148->MobilePaymentController responseHandler():::" . $ex);
      }

      $this->sendEmail($booking->order_id);
    }
    return response()->json($request->status);
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
        function ($message) use ($lodge, $booking) {
          $message->to($lodge->users->first()->email)
            ->subject('Booking Information');
          $message->from('noreply@gmail.com', 'Booking confirmation');
        }
      );
    } catch (Throwable $ex) {
      Log::info("MZERROR:line-174->MobilePaymentController sendEmail():::" . $ex);
    }
  }

  public function attachPdf($data, $lodgeRoom, $lodge, $lodgeRoomType, $guests, $noOfNights, $username)
  {
    $pdf = PDF::loadView('pdf.user-invoice', compact('data', 'lodgeRoom', 'lodge', 'lodgeRoomType', 'guests', 'noOfNights', 'username'));
    return $pdf;
  }
}
