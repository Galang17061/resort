<?php

namespace App\Http\Controllers\backend\book;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\models;
use Mail;
use Auth;
class book_roomController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    protected $models;
    public function __construct()
    {
        $this->models = new models();
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function room_detail($id)
    {
        $dt = $this->models->c_room()->where('cr_id',$id)->get();
        $data = [];
        foreach ($dt as $key => $value) {
           $data[$key] = $value;
           $data[$key]->m_type_room;
           $data[$key]->c_room_image;
           $data[$key]->c_room_features;
        }
        $type_room = $this->models->m_type_room()->get();
        return view('frontend.room.room_detail',compact('data','type_room'));
    }
    public function book_detail(Request $req,$id)
    {
        // dd($req->all());
        $dt = $this->models->c_room()->where('cr_id',$id)->get();
        $data = [];
        foreach ($dt as $key => $value) {
           $data[$key] = $value;
           $data[$key]->m_type_room;
           $data[$key]->c_room_image;
           $data[$key]->c_room_features;
        }
        $type_room = $this->models->m_type_room()->get();
        $request = $req->all();
        // return $request;
        // return $request['start_date'];
        return view('frontend.room.room_detail_book',compact('data','type_room','request'));
    }
    public function save(Request $req)
    {
        // dd($req->all());
        $email         = $req->email;
        $username      = $req->first_name.' '.$req->last_name;
        $address       = $req->address;
        $phone         = $req->phone;
        $phone1        = $req->phone1;
        $start_date    = $req->start_date;
        $end_date      = $req->end_date;
        $typename      = $req->typename;
        $total_price   = $req->total_price;
        $room_price    = $req->room_price;
        $room_id       = $req->id_room;
        $tax_price     = $req->tax_price;
        $serve_price   = $req->serve_price;
        $additional_price= $req->additional_price;
        $id_book       = $this->models->d_room_book()->max('drb_id')+1;
        $kode          = 'BKG-'.date('ym').'/'.$id_book;
        $token = str_random(70);
        $token2 = str_random(50);
        if (Auth::user() != null) {
             $id_user = Auth::user()->m_id;
             $id_guest = null;
             $id_pendaftar = Auth::user()->m_id;
        }else{
             $id_user = null;
             $id_pendaftar = $this->models->d_room_guest()->max('drbg_id')+1;
             $id_guest = $this->models->d_room_guest()->max('drbg_id')+1;
             $this->models->d_room_guest()->create([
                  'drbg_id'=>$id_guest,
                  'drbg_book_id'=>$id_book,
                  'drbg_first_name'=>$req->first_name,
                  'drbg_last_name'=>$req->last_name,
                  'drbg_address'=>$req->address,
                  'drbg_email'=>$email,
                  'drbg_phone'=>$phone,
                  'drbg_phone1'=>$phone1
             ]);
        }
        

        $this->models->d_room_book()->create([
                            'drb_id' =>$id_book,
                            'drb_code' =>$kode,
                            'drb_start_date'=>date('Y-m-d',strtotime($start_date)),
                            'drb_end_date'=>date('Y-m-d',strtotime($start_date)),
                            'drb_qty'=>$req->qty,
                            'drb_total_price'=>$total_price,
                            'drb_guest'=>$id_guest,
                            'drb_user'=>$id_user,
                            'drb_created_by'=>$id_pendaftar, 
                            'drb_room_price'=>$room_price,
                            'drb_room_id'=>$req->id_room,
                            'drb_additional_price'=>$additional_price,
                            'drb_tax_price'=>$tax_price,
                            'drb_serve_price'=>$serve_price,
                            'drb_type_bed'=>$typename,
                            'drb_created_at'=>date('Y-m-d h:i:s')
        ]);



        $room = $this->models->c_room()->where('cr_id',$req->id_room)->first();
        $room_name = $room->cr_name;
        
       
        

        $mail = Mail::send('frontend.mail.mail_verification', 
                    ['username' => $username,
                     'start_date' => $start_date,
                      'end_date' => $end_date,
                      'typename' => $typename,
                      'email' => $email,
                      'total_price' => $total_price,
                      'room_name' => $room_name,
                      'kode' => $kode,
                      'id_book' => $id_book,
                      'token' => $token,
                      'token2' => $token2,
                    ], function($message) use ($email,$start_date,$end_date,$typename,$total_price,$username,$room_name,$kode,$token,$token2,$id_book){
                        $message->from('system@ketikaku.com', 'GUNUNG BALE RESORT')
                            ->to($email)
                            ->subject('Resume Booking Room');
                    });


        return response()->json(['status'=>'sukses']);
    }
    // public function book_detail_room()
    // {
    //     return view('frontend.room.room_detail_book');
    // }

}
