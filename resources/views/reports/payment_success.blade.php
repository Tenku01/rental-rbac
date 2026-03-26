<!DOCTYPE html>
<html>
<head>
    <title>Invoice Pembayaran - AKA Rentcar</title>
</head>
<body style="font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; line-height: 1.6; color: #333; background-color: #f3f4f6; padding: 20px; margin: 0;">

    <div style="max-w: 600px; margin: 0 auto; background: #ffffff; padding: 30px; border-radius: 12px; border-top: 8px solid #0891b2; box-shadow: 0 4px 6px rgba(0,0,0,0.05);">
        
        <table width="100%" cellpadding="0" cellspacing="0" style="border-bottom: 2px dashed #e5e7eb; padding-bottom: 20px; margin-bottom: 20px;">
            <tr>
                <td valign="top">
                    <img src="{{ $message->embed(public_path('logoakarentcar.png')) }}" alt="Logo AKA Rentcar" style="max-height: 50px; margin-bottom: 15px; display: block;">
                    
                    <h2 style="color: #0891b2; margin: 0; font-size: 28px; letter-spacing: 1px;">INVOICE</h2>
                    <p style="margin: 5px 0 0; color: #6b7280; font-size: 14px;">#{{ $payment->midtrans_transaction_id }}</p>
                    <p style="margin: 5px 0 0; font-size: 13px; color: #9ca3af;">{{ \Carbon\Carbon::parse($payment->created_at)->translatedFormat('d F Y, H:i') }} WIB</p>
                </td>
                <td align="right" valign="top">
                    <span style="display: inline-block; background-color: #d1fae5; color: #047857; padding: 6px 12px; border-radius: 20px; font-size: 12px; font-weight: bold; border: 1px solid #a7f3d0;">
                        &#10003; Pembayaran Berhasil
                    </span>
                </td>
            </tr>
        </table>

        <table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom: 25px;">
            <tr>
                <td width="50%" valign="top" style="padding-right: 10px;">
                    <h3 style="font-size: 11px; color: #9ca3af; text-transform: uppercase; margin: 0 0 5px; letter-spacing: 1px;">Diterbitkan Kepada:</h3>
                    <p style="margin: 0; font-weight: bold; color: #1f2937; font-size: 16px;">{{ $payment->peminjaman->user->name }}</p>
                    <p style="margin: 3px 0 0; color: #4b5563; font-size: 14px;">{{ $payment->peminjaman->user->email }}</p>
                </td>
                <td width="50%" valign="top" align="right">
                    <h3 style="font-size: 11px; color: #9ca3af; text-transform: uppercase; margin: 0 0 5px; letter-spacing: 1px;">Informasi Transaksi:</h3>
                    <p style="margin: 0; color: #4b5563; font-size: 14px;"><strong style="color: #1f2937;">Tipe:</strong> {{ $payment->tipe_transaksi === 'dp' ? 'Down Payment (DP)' : ($payment->tipe_transaksi === 'sisa' ? 'Pelunasan Sisa' : 'Pembayaran Lunas') }}</p>
                    <p style="margin: 3px 0 0; color: #4b5563; font-size: 14px;"><strong style="color: #1f2937;">Metode:</strong> {{ ucfirst($payment->peminjaman->metode_pembayaran) }}</p>
                    <p style="margin: 3px 0 0; color: #4b5563; font-size: 14px;"><strong style="color: #1f2937;">Booking ID:</strong> #{{ $payment->peminjaman_id }}</p>
                </td>
            </tr>
        </table>

        <div style="background-color: #f9fafb; border: 1px solid #f3f4f6; border-radius: 8px; padding: 20px; margin-bottom: 25px;">
            <h3 style="margin: 0 0 15px; color: #155e75; font-size: 16px; border-bottom: 1px solid #e5e7eb; padding-bottom: 10px;">Detail Reservasi Kendaraan</h3>
            
            <table width="100%" cellpadding="0" cellspacing="0" style="font-size: 14px; color: #4b5563;">
                <tr>
                    <td style="padding: 6px 0;">Mobil:</td>
                    <td align="right" style="padding: 6px 0; color: #1f2937;"><strong>{{ $payment->peminjaman->mobil->merek }} {{ $payment->peminjaman->mobil->tipe }}</strong></td>
                </tr>
                <tr>
                    <td style="padding: 6px 0;">Plat Nomor:</td>
                    <td align="right" style="padding: 6px 0; color: #1f2937;"><strong>{{ $payment->peminjaman->mobil_id }}</strong></td>
                </tr>
                <tr>
                    <td style="padding: 6px 0;">Layanan:</td>
                    <td align="right" style="padding: 6px 0; color: #0891b2;"><strong>{{ $payment->peminjaman->add_on_sopir ? 'Dengan Sopir' : 'Lepas Kunci' }}</strong></td>
                </tr>
                <tr>
                    <td style="padding: 6px 0;">Waktu Ambil:</td>
                    <td align="right" style="padding: 6px 0; color: #1f2937;">{{ \Carbon\Carbon::parse($payment->peminjaman->tanggal_sewa)->translatedFormat('d M Y') }} <span style="color: #0891b2; font-size: 12px;">({{ $payment->peminjaman->jam_sewa }} WIB)</span></td>
                </tr>
                <tr>
                    <td style="padding: 6px 0;">Waktu Kembali:</td>
                    <td align="right" style="padding: 6px 0; color: #1f2937;">{{ \Carbon\Carbon::parse($payment->peminjaman->tanggal_kembali)->translatedFormat('d M Y') }} <span style="color: #0891b2; font-size: 12px;">({{ $payment->peminjaman->jam_sewa }} WIB)</span></td>
                </tr>
            </table>
        </div>

        <div style="margin-bottom: 30px;">
            <h3 style="margin: 0 0 15px; color: #155e75; font-size: 16px; border-bottom: 1px solid #e5e7eb; padding-bottom: 10px;">Rincian Pembayaran</h3>
            
            <table width="100%" cellpadding="0" cellspacing="0" style="font-size: 14px; color: #4b5563;">
                <tr>
                    <td style="padding: 8px 0;">Total Harga Sewa Keseluruhan</td>
                    <td align="right" style="padding: 8px 0; color: #1f2937;"><strong>Rp {{ number_format($payment->peminjaman->total_harga, 0, ',', '.') }}</strong></td>
                </tr>
                
                @if($payment->peminjaman->sisa_bayar > 0)
                <tr>
                    <td style="padding: 8px 0;">Total Telah Dibayar <span style="font-size:12px; color:#9ca3af;">(Termasuk trx ini)</span></td>
                    <td align="right" style="padding: 8px 0; color: #1f2937;"><strong>Rp {{ number_format($payment->peminjaman->total_dibayarkan, 0, ',', '.') }}</strong></td>
                </tr>
                <tr>
                    <td style="padding: 8px 0; color: #dc2626;"><strong>Sisa Tagihan Belum Dibayar</strong></td>
                    <td align="right" style="padding: 8px 0; color: #dc2626;"><strong>Rp {{ number_format($payment->peminjaman->sisa_bayar, 0, ',', '.') }}</strong></td>
                </tr>
                @endif
                
                <tr>
                    <td colspan="2" style="border-bottom: 2px dashed #d1d5db; padding-top: 15px;"></td>
                </tr>
                <tr>
                    <td style="padding: 20px 0 0;"><strong style="font-size: 16px; color: #1f2937;">Nominal Transaksi Ini</strong></td>
                    <td align="right" style="padding: 20px 0 0; color: #0891b2; font-size: 24px;"><strong>Rp {{ number_format($payment->amount, 0, ',', '.') }}</strong></td>
                </tr>
            </table>
        </div>

        <table width="100%" cellpadding="0" cellspacing="0">
            <tr>
                <td align="center">
                    <a href="{{ route('pesanan.saya') }}" style="display: inline-block; background-color: #0891b2; color: #ffffff; padding: 12px 30px; text-decoration: none; border-radius: 8px; font-weight: bold; font-size: 15px;">Lihat Pesanan Saya</a>
                </td>
            </tr>
        </table>

        <div style="margin-top: 40px; padding-top: 20px; border-top: 1px solid #e5e7eb; text-align: center; font-size: 12px; color: #9ca3af;">
            <p style="margin: 0 0 5px;">Simpan email ini sebagai bukti transaksi yang sah.</p>
            <p style="margin: 0 0 5px;">Jika Anda memiliki pertanyaan, silakan hubungi customer service kami.</p>
            <p style="margin: 15px 0 0; font-weight: bold; color: #6b7280;">&copy; {{ date('Y') }} AKA Rentcar.</p>
        </div>

    </div>

</body>
</html>