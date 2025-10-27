<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Sertifikat Kelas</title>
<style>
    body {
        font-family: 'Arial', sans-serif;
        margin: 0;
        padding: 0;
        background: white;
    }
    .certificate-container {
        border: 5px solid #0A74DA;
        padding: 20px;
        width: 1000px;
        height: 700px;
        margin: 50px auto;
        position: relative;
    }
    .certificate-header {
        text-align: center;
        margin-top: 50px;
        margin-bottom: 50px;
    }
    .certificate-body {
        margin-left: 50px;
        margin-right: 50px;
    }
    .certificate-title {
        font-size: 48px;
        color: #0A74DA;
        font-weight: bold;
    }
    .participant-name {
        font-size: 30px;
        margin-top: 20px;
    }
    .course-name {
        font-size: 24px;
        margin-top: 5px;
        margin-bottom: 20px;
    }
    .certificate-footer {
        position: absolute;
        bottom: 50px;
        left: 50px;
        right: 50px;
        text-align: center;
    }
    .footer-text {
        font-size: 16px;
        margin-top: 5px;
    }
    .signature {
        font-family: 'Homemade Apple', cursive;
        font-size: 24px;
        text-align: right;
        margin-right: 50px;
    }
    .qr-code {
        position: absolute;
        bottom: 50px;
        left: 50px;
    }
</style>
</head>
<body>
<div class="certificate-container">
    <div class="certificate-header">
        <!-- Logo image here -->
        <!-- <img src="{{ asset('path-to-your-logo.png') }}" alt="Logo" height="100"> -->
        <img src="https://kelasdrafter.id/_next/image?url=%2Fassets%2Ficons%2Flogo-sidebar.png&w=48&q=75" alt="Logo" height="100">
    </div>
    <div class="certificate-body">
        <p class="certificate-title">CERTIFICATE OF COMPLETION</p>
        <p class="participant-name">{{ $participantName }}</p>
        <p class="course-name">{{ $courseName }}</p>
        <p class="footer-text">Course was implemented on ({{ $date }})</p>
        <p class="footer-text">"By continuing to learn, you have expanded your perspective, sharpened your skills, and made yourself even more in demand"</p>
    </div>
    <div class="certificate-footer">
        <div class="qr-code">
            <!-- QR Code image here -->
            <!-- <img src="{{ asset('path-to-your-qr-code.png') }}" alt="QR Code" height="100"> -->
            <img src="https://kelasdrafter.id/_next/image?url=%2Fassets%2Ficons%2Flogo-sidebar.png&w=48&q=75" alt="QR Code" height="100">
        </div>
        <div>
            <p class="signature">Muhammad Rosim, S.T.</p>
            <p>Founder of Kelas Drafter</p>
            <p class="signature">Muhammad Kisin JR</p>
            <p>Head Mentor of Kelas Drafter</p>
        </div>
    </div>
</div>
</body>
</html>
