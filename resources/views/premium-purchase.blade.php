<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pembelian Premium Berhasil</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f4f7fa;
            padding: 20px;
            line-height: 1.6;
        }
        
        .email-container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        
        .email-header {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            color: #ffffff;
            padding: 40px 30px;
            text-align: center;
        }
        
        .email-header h1 {
            font-size: 28px;
            margin-bottom: 10px;
            font-weight: 700;
        }
        
        .email-header p {
            font-size: 16px;
            opacity: 0.95;
        }
        
        .email-body {
            padding: 40px 30px;
        }
        
        .greeting {
            font-size: 18px;
            color: #333333;
            margin-bottom: 20px;
        }
        
        .greeting strong {
            color: #f59e0b;
        }
        
        .message {
            font-size: 15px;
            color: #555555;
            margin-bottom: 30px;
            line-height: 1.8;
        }
        
        .product-info {
            background: linear-gradient(135deg, #fffbeb 0%, #fef3c7 100%);
            border-left: 4px solid #f59e0b;
            padding: 25px;
            border-radius: 8px;
            margin-bottom: 30px;
        }
        
        .product-info h2 {
            font-size: 20px;
            color: #d97706;
            margin-bottom: 15px;
            font-weight: 600;
        }
        
        .info-row {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #fde68a;
        }
        
        .info-row:last-child {
            border-bottom: none;
        }
        
        .info-label {
            font-size: 14px;
            color: #666666;
            font-weight: 500;
        }
        
        .info-value {
            font-size: 14px;
            color: #333333;
            font-weight: 600;
            text-align: right;
        }
        
        .cta-button {
            display: block;
            width: 100%;
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            color: #ffffff;
            text-align: center;
            padding: 16px 30px;
            text-decoration: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            margin: 30px 0;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(245, 158, 11, 0.4);
        }
        
        .cta-button:hover {
            box-shadow: 0 6px 20px rgba(245, 158, 11, 0.6);
            transform: translateY(-2px);
        }
        
        .transaction-details {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-top: 30px;
        }
        
        .transaction-details h3 {
            font-size: 16px;
            color: #333333;
            margin-bottom: 15px;
            font-weight: 600;
        }
        
        .detail-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            font-size: 14px;
        }
        
        .detail-label {
            color: #666666;
        }
        
        .detail-value {
            color: #333333;
            font-weight: 600;
        }
        
        .premium-badge {
            display: inline-block;
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            color: #ffffff;
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            margin-bottom: 20px;
        }
        
        .features-list {
            background-color: #fffbeb;
            padding: 20px;
            border-radius: 8px;
            margin-top: 20px;
        }
        
        .features-list h3 {
            font-size: 16px;
            color: #d97706;
            margin-bottom: 15px;
            font-weight: 600;
        }
        
        .features-list ul {
            list-style: none;
            padding: 0;
        }
        
        .features-list li {
            padding: 8px 0;
            color: #555555;
            font-size: 14px;
        }
        
        .features-list li:before {
            content: "✓ ";
            color: #10b981;
            font-weight: bold;
            margin-right: 8px;
        }
        
        .footer-note {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 2px solid #e9ecef;
            font-size: 13px;
            color: #666666;
            line-height: 1.6;
        }
        
        .email-footer {
            background-color: #f8f9fa;
            padding: 30px;
            text-align: center;
            color: #666666;
            font-size: 13px;
        }
        
        .email-footer p {
            margin: 5px 0;
        }
        
        .email-footer strong {
            color: #f59e0b;
        }
        
        /* Responsive Design */
        @media only screen and (max-width: 600px) {
            .email-container {
                border-radius: 0;
            }
            
            .email-header {
                padding: 30px 20px;
            }
            
            .email-header h1 {
                font-size: 24px;
            }
            
            .email-body {
                padding: 30px 20px;
            }
            
            .product-info {
                padding: 20px;
            }
            
            .info-row {
                flex-direction: column;
                gap: 5px;
            }
            
            .info-value {
                text-align: left;
            }
            
            .detail-row {
                flex-direction: column;
                gap: 5px;
            }
        }
    </style>
</head>
<body>
    <div class="email-container">
        <!-- Header -->
        <div class="email-header">
            <h1>🎉 Pembelian Berhasil!</h1>
            <p>Produk Premium Anda Sudah Siap</p>
        </div>
        
        <!-- Body -->
        <div class="email-body">
            <!-- Premium Badge -->
            <span class="premium-badge">⭐ PREMIUM ACCESS</span>
            
            <!-- Greeting -->
            <div class="greeting">
                Halo, <strong>{{ $user->name }}</strong>! 👋
            </div>
            
            <!-- Message -->
            <div class="message">
                Terima kasih atas pembelian produk premium Anda! Pembayaran telah berhasil dikonfirmasi. 
                Sekarang Anda dapat mengakses dan mengunduh produk eksklusif yang telah Anda beli. 
                Nikmati konten berkualitas tinggi yang telah disiapkan khusus untuk Anda!
            </div>
            
            <!-- Product Info -->
            <div class="product-info">
                <h2>🎁 Detail Produk Premium</h2>
                <div class="info-row">
                    <span class="info-label">Nama Produk</span>
                    <span class="info-value">{{ $product->name }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Kategori</span>
                    <span class="info-value">{{ $product->category ?? 'Premium Product' }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Status Akses</span>
                    <span class="info-value" style="color: #10b981;">✓ Aktif Selamanya</span>
                </div>
            </div>
            
            <!-- CTA Button -->
            @if($downloadUrl)
            <a href="{{ $downloadUrl }}" class="cta-button">
                ⬇️ Download Produk Sekarang
            </a>
            @else
            <a href="{{ config('app.url') }}/dashboard" class="cta-button">
                🔐 Akses Dashboard Saya
            </a>
            @endif
            
            <!-- Features List -->
            <div class="features-list">
                <h3>✨ Yang Anda Dapatkan:</h3>
                <ul>
                    <li>Akses seumur hidup ke produk premium</li>
                    <li>Konten berkualitas tinggi dan eksklusif</li>
                    <li>Update gratis untuk versi mendatang</li>
                    <li>Dukungan prioritas dari tim kami</li>
                </ul>
            </div>
            
            <!-- Transaction Details -->
            <div class="transaction-details">
                <h3>📋 Detail Transaksi</h3>
                <div class="detail-row">
                    <span class="detail-label">ID Transaksi</span>
                    <span class="detail-value">{{ $transaction->id }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Total Pembayaran</span>
                    <span class="detail-value">Rp {{ number_format($transaction->amount, 0, ',', '.') }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Tanggal Pembelian</span>
                    <span class="detail-value">{{ \Carbon\Carbon::parse($transaction->created_at)->format('d M Y, H:i') }} WIB</span>
                </div>
            </div>
            
            <!-- Footer Note -->
            <div class="footer-note">
                <p>
                    <strong>💡 Catatan Penting:</strong> Simpan email ini sebagai bukti pembelian Anda. 
                    Link download akan tetap aktif dan dapat diakses kapan saja dari dashboard Anda.
                </p>
                <p style="margin-top: 15px;">
                    Jika Anda mengalami kendala dalam mengunduh atau mengakses produk, 
                    silakan hubungi tim support kami untuk bantuan.
                </p>
            </div>
        </div>
        
        <!-- Footer -->
        <div class="email-footer">
            <p><strong>Kelas Drafter</strong></p>
            <p>Platform Pembelajaran Online Terpercaya</p>
            <p style="margin-top: 10px;">© {{ date('Y') }} Kelas Drafter. All rights reserved.</p>
        </div>
    </div>
</body>
</html>