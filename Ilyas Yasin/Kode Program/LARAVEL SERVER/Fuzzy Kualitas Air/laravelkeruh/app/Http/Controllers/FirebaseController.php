<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Firebase\Firebase;
use Carbon\Carbon;
use DateTime;

class FirebaseController extends Controller
{
    public function PushServer(Request $request){
           $kekeruhan = 21;
           $suhu = 25;
           // $kekeruhan =  $request->input('kr');
           $idarduino = 'AR01';
           $timeNow = Carbon::now()->timestamp;
           $milis = $timeNow % 1000;
            $ts = intval($timeNow / 1000);
            $date = DateTime::createFromFormat('U', $ts);
            $str = $date->format(date('d-m-Y_H:i:s'));
           $fb = Firebase::initialize("");
           // $kualitasair = $request->input('kl');

            if ($kekeruhan < 10 && $suhu < 10 ) {
                 $kualitasair = 'Jernih'; 
                 $kondisisuhu = 'Rendah';
                 $kekeruhan [0] = 0;
                 $suhu [0] = 0;
                 $nodePushContent = $fb->set('/Kekeruhan/'.$idarduino.'/'.$timeNow, [
                                    'Kekeruhan'   => $kekeruhan,
                                    'Kualitasair' => $kualitasair,
                                    'Suhu' => $suhu,
                                    'Kondisi'=> $kondisisuhu,
                  ]);
            }
            if ($kekeruhan > 20 && $kekeruhan < 25 && $suhu > 20 && $suhu < 30){
                 $kualitasair = 'Normal';
                   $kondisisuhu = 'Normal';
                   $kekeruhan [1] = 0;
                 $suhu [1] = 0;
                 $nodePushContent = $fb->set('/Kekeruhan/'.$idarduino.'/'.$timeNow, [
                                    'Kekeruhan'   => $kekeruhan,
                                    'Kualitasair' => $kualitasair,
                                    'Suhu' => $suhu,
                                    'Kondisi'=> $kondisisuhu,
                  ]);
            }
            if ($kekeruhan > 25 && $kekeruhan < 30 && $suhu > 30 && $suhu < 40 ) {
                 $kualitasair = 'Keruh';
                 $kondisisuhu = 'Tinggi';
                 $kekeruhan [2] = 1;
                 $suhu [2] = 1;
                 $nodePushContent = $fb->set('/Kekeruhan/'.$idarduino.'/'.$timeNow, [
                                    'Kekeruhan'   => $kekeruhan,
                                    'Kualitasair' => $kualitasair,
                                    'Suhu' => $suhu,
                                    'Kondisi'=> $kondisisuhu,
                  ]);
            // Untuk mengirim Notifikasi ke Android apabila Nilai kekekruhan meningkat hight or Keruh
            $url = "https://fcm.googleapis.com/fcm/send";
            $token = "";
            $serverKey = '';
            $title = $kekeruhan;
            $body = $kualitasair;
            $notification = array('title' =>$title , 'body' => $body, 'sound' => 'default', 'badge' => '1');
            $arrayToSend = array('to' => $token, 'notification' => $notification,'priority'=>'high');
            $json = json_encode($arrayToSend);
            $headers = array();
            $headers[] = 'Content-Type: application/json';
            $headers[] = 'Authorization: key='. $serverKey;
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST,"POST");
            curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
            curl_setopt($ch, CURLOPT_HTTPHEADER,$headers);
            //Send the request
            $response = curl_exec($ch);
            //Close request
            if ($response === FALSE) {
            die('FCM Send Error: ' . curl_error($ch));
            }
            curl_close($ch);
        }
        return response()->json('Insert Berhasil');
    }




    public function fuzzy(){
        //fizzyfication
        $suhu = 21;
        $kekeruhan = 115;
        $idarduino = 'AR01';
           $timeNow = Carbon::now()->timestamp;
           $milis = $timeNow % 1000;
            $ts = intval($timeNow / 1000);
            $date = DateTime::createFromFormat('U', $ts);
            $str = $date->format(date('d-m-Y_H:i:s'));
           $fb = Firebase::initialize("");

        if ($suhu < 90){
            $kondisi[0] = 1;
        } else if ($suhu > 90 && $suhu < 110){
             $kondisi[0] = (110 - $suhu)/(110 - 90);
        } else {
            $kondisi[0] = 0;
        }  
        
        // normal
        if ($suhu < 90){
             $kondisi[1] = 0;
        } else if ($suhu > 90 && $suhu < 110){
             $kondisi[1] = ($suhu - 90)/(110 - 90);
        } else if ($suhu > 110 && $suhu < 125){
             $kondisi[1] = (125 - $suhu)/(125 - 110);
        } else {
             $kondisi[1] = 0;
        } 
        // tinggi
        if ($suhu < 110){
            $kondisi [2] = 0;
        } else if ($suhu > 110 && $suhu < 125){
             $kondisi[2] = ($suhu-110)/(125 - 110);
        } else {
            $kondisi [2] = 1;
        }

        //kekeruhan rendah
        if ($kekeruhan < 90){
            $sikon[0] = 1;
        } else if ($kekeruhan > 90 && $kekeruhan < 110){
             $kekeruhan[0] = (110 - $kekeruhan)/(110 - 90);
        } else {
            $sikon[0] = 0;
        }  
        
        // normal
        if ($kekeruhan < 110){
             $sikon[1] = 0;
        } else if ($kekeruhan > 90 && $kekeruhan < 110){
             $sikon[1] = ($kekeruhan-90)/(110 - 90);
        } else if ($kekeruhan > 110 && $kekeruhan < 125){
             $sikon[1] = (125-$kekeruhan)/(125 - 110);
        } else {
             $sikon[1] = 0;
        } 
        // tinggi
        if ($kekeruhan < 110){
            $sikon[2] = 0;
        } else if ($kekeruhan > 110 && $kekeruhan < 125){
             $sikon[2] = ($kekeruhan - 110)/(125 - 110);
        } else {
            $sikon[2] = 1;
        }

        //rolues fuzzy
       $i; $j;
for ($i=0; $i<=2; $i=$i+1)
{
     for($j=0; $j<=2; $j=$j+1)
     {
         $kualair = min($kondisi[$i], $sikon[$j]);
        $rule [$i][$j] = $kualair;    
     }
} 

    $rules0 = $rule [0][0]; // (jernih, sedang = jernih)
    $rules1 = $rule [0][1]; // (jernih, normal = jernih)
    $rules2 = $rule [0][2]; // (jernih, tinggi = jernih)
          
    $rules3 = $rule [1][0]; // (normal, sedang = jernih)
    $rules4 = $rule [1][1]; // (normal, normal = normal)
    $rules5 = $rule [1][2]; // (normal, tinggi = keruh)

    $rules6 = $rule [2][0]; // (keruh, sedang = keruh)
    $rules7 = $rule [2][1]; // (keruh, normal = keruh)
    $rules8 = $rule [2][2]; // (keruh, tinggi = keruh)


    //Defuzzyfication

    $rendah = 40;
    $normal = 80;
    $keruh  = 103;

    $ruleskualitas = ($rules0 * $rendah) + ($rules1 * $rendah) + ($rules2 * $rendah) + 
    ($rules3 * $normal) + ($rules4 * $normal) + ($rules5 * $normal) +
    ($rules6 * $keruh) + ($rules7 * $keruh) + ($rules8 * $keruh);

        $defus = 0;
        $i; $j;
         for($i=0; $i<2; $i=$i+1 )
         {
                for($j=0; $j<2; $j=$j+1)
                {
                    $defus = $defus + $rule[$i][$j];
                }
        }
         $result = $ruleskualitas / $defus;

         if ($result==10){
             $kualitasair = 'rendah';
         }

         if ($result==60){
             $kualitasair = 'normal';
         }

         if($result==45){

            $kualitasair = 'tinggi';
            $url = "https://fcm.googleapis.com/fcm/send";
            $token = '';
            $serverKey = '';
            $title = $result;
            $body = $kualitasair;
            $notification = array('title' =>$title , 'body' => $body, 'sound' => 'default', 'badge' => '1');
            $arrayToSend = array('to' => $token, 'notification' => $notification,'priority'=>'high');
            $json = json_encode($arrayToSend);
            $headers = array();
            $headers[] = 'Content-Type: application/json';
            $headers[] = 'Authorization: key='. $serverKey;
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST,"POST");
            curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
            curl_setopt($ch, CURLOPT_HTTPHEADER,$headers);
            //Send the request
            $response = curl_exec($ch);
            //Close request
            if ($response === FALSE) {
            die('FCM Send Error: ' . curl_error($ch));
            }
            curl_close($ch);
         } 
            $nodePushContent = $fb->set('/Kekeruhan/'.$idarduino.'/'.$timeNow, [
                                        'kualitasair'   => $result,
                                        'kondisi' => $kualitasair,
            ]);
    }

    public function fuzzy2(){
                //fizzyfication
        $suhu = 15;
        $kekeruhan = 22;
        $idarduino = 'AR01';
           $timeNow = Carbon::now()->timestamp;
           $milis = $timeNow % 1000;
            $ts = intval($timeNow / 1000);
            $date = DateTime::createFromFormat('U', $ts);
            $str = $date->format(date('d-m-Y_H:i:s'));
           $fb = Firebase::initialize("https://kekeruhanhydroponics.firebaseio.com/");

        if ($suhu < 10){
            $kondisi[0] = 1;
        } else if ($suhu > 10 && $suhu < 20){
             $kondisi[0] = (20 - $suhu)/(20 - 10);
        } else {
            $kondisi[0] = 0;
        }  
        
        // normal
        if ($suhu < 10){
             $kondisi[1] = 0;
        } else if ($suhu > 10 && $suhu < 20){
             $kondisi[1] = ($suhu - 10)/(20 - 10);
        } else if ($suhu > 20 && $suhu < 40){
             $kondisi[1] = (40 - $suhu)/(40 - 20);
        } else {
             $kondisi[1] = 0;
        } 

        // tinggi
        if ($suhu < 20){
            $kondisi [2] = 0;
        } else if ($suhu > 20 && $suhu < 30){
             $kondisi[2] = ($suhu-20)/(30 - 20);
        } else if ($suhu > 30) {
            $kondisi [2] = 1;
        }

        //kekeruhan 


        //rendah
        if ($kekeruhan < 10){
            $sikon[0] = 1;
        } else if ($kekeruhan > 10 && $kekeruhan < 20){
             $kekeruhan[0] = (20 - $kekeruhan)/(20 - 10);
        } else {
            $sikon[0] = 0;
        }  
        
        // normal
        if ($kekeruhan < 20){
             $sikon[1] = 0;
        } else if ($kekeruhan > 10 && $kekeruhan < 20){
             $sikon[1] = ($kekeruhan-10)/(20 - 10);
        } else if ($kekeruhan > 20 && $kekeruhan < 40){
             $sikon[1] = (40-$kekeruhan)/(40 - 20);
        } else {
             $sikon[1] = 0;
        } 
        // tinggi
        if ($kekeruhan < 20){
            $sikon[2] = 0;
        } else if ($kekeruhan > 20 && $kekeruhan < 30){
             $sikon[2] = ($kekeruhan - 20)/(30 - 20);
        } else if ($kekeruhan > 30) {
            $sikon[2] = 1;
        }

        //rolues fuzzy
       $i; $j;
for ($i=0; $i<=2; $i=$i+1)
{
     for($j=0; $j<=2; $j=$j+1)
     {
         $kualair = min($kondisi[$i], $sikon[$j]);
        $rule [$i][$j] = $kualair;    
     }
} 
    $rules0 = $rule [0][0]; // (jernih, rendah = jernih)
    $rules1 = $rule [0][1]; // (jernih, sedang = jernih)
    $rules2 = $rule [0][2]; // (jernih, tinggi = jernih)
          
    $rules3 = $rule [1][0]; // (normal, rendah = jernih)
    $rules4 = $rule [1][1]; // (normal, sedang = normal)
    $rules5 = $rule [1][2]; // (normal, tinggi = keruh)

    $rules6 = $rule [2][0]; // (keruh, rendah = keruh)
    $rules7 = $rule [2][1]; // (keruh, sedang = keruh)
    $rules8 = $rule [2][2]; // (keruh, tinggi = keruh)

    //Defuzzyfication

    $rendah = 10;
    $normal = 20;
    $keruh  = 40;

    $ruleskualitas = ($rules0 * $rendah) + ($rules1 * $rendah) + ($rules2 * $rendah) + 
    ($rules3 * $normal) + ($rules4 * $normal) + ($rules5 * $normal) +
    ($rules6 * $keruh) + ($rules7 * $keruh) + ($rules8 * $keruh);

        $defus = 0;
        $i; $j;
         for($i=0; $i<2; $i=$i+1 )
         {
                for($j=0; $j<2; $j=$j+1)
                {
                    $defus = $defus + $rule[$i][$j];
                }
        }
         $result = $ruleskualitas / $defus;

         if ($result > 10 && $result < 20){
             $kualitasair = 'rendah';
         }

         if ($result > 20 && $result < 30 ){
             $kualitasair = 'normal';
         }

         if ($result > 30 && $result < 40){

            $kualitasair = 'tinggi';
            $url = "https://fcm.googleapis.com/fcm/send";
            $token = "";
            $serverKey = '';
            $title = $result;
            $body = $kualitasair;
            $notification = array('title' =>$title , 'body' => $body, 'sound' => 'default', 'badge' => '1');
            $arrayToSend = array('to' => $token, 'notification' => $notification,'priority'=>'high');
            $json = json_encode($arrayToSend);
            $headers = array();
            $headers[] = 'Content-Type: application/json';
            $headers[] = 'Authorization: key='. $serverKey;
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST,"POST");
            curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
            curl_setopt($ch, CURLOPT_HTTPHEADER,$headers);
            //Send the request
            $response = curl_exec($ch);
            //Close request
            if ($response === FALSE) {
            die('FCM Send Error: ' . curl_error($ch));
            }
            curl_close($ch);
         } 
            $nodePushContent = $fb->set('/Kekeruhan/'.$idarduino.'/'.$timeNow, [
                                        'kondisinow'   => $result,
                                        'kondisi' => $kualitasair,
                                        
            ]);
    }
}
