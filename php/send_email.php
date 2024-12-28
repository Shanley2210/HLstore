<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Include các file PHPMailer cần thiết
require '../mail/PHPMailer/src/Exception.php';
require '../mail/PHPMailer/src/PHPMailer.php';
require '../mail/PHPMailer/src/SMTP.php';

function sendInvoiceEmail($tenkh, $emailkh, $mahd, $totalWithShipping, $pt_thanhtoan, $diachi, $sdt, $orderItems) {
    $mail = new PHPMailer(true);

    try {
        // Cấu hình server SMTP
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'khanhlinhhuynh457@gmail.com'; // Email của bạn
        $mail->Password = 'lnbgwekqhgmjzcif'; // Mật khẩu ứng dụng
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        // Cấu hình người gửi và người nhận
        $mail->setFrom('khanhlinhhuynh457@gmail.com', 'Web Bán Sách - HLSTORE'); // Email gửi
        $mail->addAddress($emailkh, $tenkh); // Email người nhận

        // Thiết lập mã hóa UTF-8
        $mail->CharSet = 'UTF-8';
        $mail->Encoding = 'base64';

        // Tạo nội dung email
        $emailContent = "    
            <table border='1' cellpadding='5' cellspacing='0' style='border-collapse: collapse; width: 100%;'>
                <tr>
                    <th>Sản phẩm</th>
                    <th>Số lượng</th>
                    <th>Đơn giá</th>
                    <th>Thành tiền</th>
                </tr>";

        foreach ($orderItems as $item) {
            $donGia = number_format($item['dongia'], 0, ',', '.');
            $thanhTien = number_format($item['tongtien'], 0, ',', '.');
            $emailContent .= "
                <tr>
                    <td>{$item['tensach']}</td>
                    <td>{$item['soluongsp']}</td>
                    <td>{$donGia}đ</td>
                    <td>{$thanhTien}đ</td>
                </tr>";
        }

        $totalWithShippingFormat = number_format($totalWithShipping, 0, ',', '.');
        $emailContent .= "
            <tr>
                <td colspan='3'><strong>Tổng cộng (bao gồm phí vận chuyển):</strong></td>
                <td><strong>{$totalWithShippingFormat}đ</strong></td>
            </tr>
            </table>";

        // Nội dung email
        $mail->isHTML(true);
        $mail->Subject = "Hóa đơn đặt hàng #$mahd";
        $mail->Body = "
            <html>
            <head>
                <meta charset='UTF-8'>
            </head>
            <body>
                <h2>Thông tin hóa đơn</h2>
                <p>Xin chào <b>{$tenkh}</b>,</p>
                <p>Cảm ơn bạn đã đặt hàng tại cửa hàng của chúng tôi. Dưới đây là thông tin hóa đơn:</p>
                <p><b>Mã hóa đơn:</b> $mahd</p>
                <p><b>Số điện thoại:</b> $sdt</p>
                <p><b>Địa chỉ giao hàng:</b> $diachi</p>
                <p><b>Phương thức thanh toán:</b> $pt_thanhtoan</p>
                <p><b>Tổng tiền (bao gồm phí vận chuyển):</b> " . number_format($totalWithShipping, 0, ',', '.') . " VND</p>
                <h3>SẢN PHẨM MUA:</h3>
                $emailContent
                <p>Chúng tôi sẽ liên hệ với bạn sớm nhất để giao hàng!</p>
            </body>
            </html>
        ";

        // Gửi email
        $mail->send();
        return true; // Gửi thành công
    } catch (Exception $e) {
        return "Không thể gửi email. Lỗi: {$mail->ErrorInfo}";
    }
}
