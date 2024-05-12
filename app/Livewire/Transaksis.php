<?php

namespace App\Livewire;

use Exception;
use App\Models\transaksi;
use App\Models\layanan;
use Livewire\Component;
use App\Models\detiltransaksi;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;

class transaksis extends Component
{
    public $total;
    public $transaksi_id;
    public $layanan_id;
    public $jumlah=1;
    public $uang;
    public $kembali;

    public function render()
    {
        $transaksi=transaksi::select('*')->where('user_id','=',Auth::user()->id)->orderBy('id','desc')->first();

        $this->total=$transaksi->total;
        $this->kembali=$this->uang-$this->total;
        return view('livewire.transaksis')
        ->with("data",$transaksi)
        ->with("datalayanan",layanan::where('stock','>','0')->get())
        ->with("datadetiltransaksi",detiltransaksi::where('transaksi_id','=',$transaksi->id)->get());
    }

    public function store()
    {
        $this->validate([
            
            'layanan_id'=>'required'
        ]);
        $transaksi=transaksi::select('*')->where('user_id','=',Auth::user()->id)->orderBy('id','desc')->first();
        $this->transaksi_id=$transaksi->id;
        $layanan=layanan::where('id','=',$this->layanan_id)->get();
        $harga=$layanan[0]->price;
        detiltransaksi::create([
            'transaksi_id'=>$this->transaksi_id,
            'layanan_id'=>$this->layanan_id,
            'jumlah'=>$this->jumlah,
            'price'=>$harga
        ]);
        
        
        $total=$transaksi->total;
        $total=$total+($harga*$this->jumlah);
        transaksi::where('id','=',$this->transaksi_id)->update([
            'total'=>$total
        ]);
        $this->layanan_id=NULL;
        $this->jumlah=1;

    }

    public function delete($detiltransaksi_id)
    {
        $detiltransaksi=detiltransaksi::find($detiltransaksi_id);
        $detiltransaksi->delete();

        //update total
        $detiltransaksi=detiltransaksi::select('*')->where('transaksi_id','=',$this->transaksi_id)->get();
        $total=0;
        foreach($detiltransaksi as $od){
            $total+=$od->jumlah*$od->price;
        }
        
        try{
            transaksi::where('id','=',$this->transaksi_id)->update([
                'total'=>$total
            ]);
        }catch(Exception $e){
            dd($e);
        }
    }
    
    public function receipt($id)
    {
        $detiltransaksi = detiltransaksi::select('*')->where('transaksi_id','=', $id)->get();
        //dd ($detiltransaksi);
        foreach ($detiltransaksi as $od) {
            $stocklama = layanan::select('stock')->where('id','=', $od->layanan_id)->sum('stock');
            $stock = $stocklama - $od->jumlah;
            try {
                layanan::where('id','=', $od->layanan_id)->update([
                    'stock' => $stock
                ]);
            } catch (Exception $e) {
                dd($e);
            }
        }
        return Redirect::route('cetakReceipt')->with(['id' => $id]);

    }

}
