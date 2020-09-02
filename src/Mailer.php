<?php
namespace App;

use PHPMailer\PHPMailer\PHPMailer;

final class Mailer
{
	/**
	 * @throws \PHPMailer\PHPMailer\Exception
	 */
	public static function sendError(string $error): void
	{
		$mail = self::createEmail();
		$mail->Subject = 'Bol.com Customers Downloader Error';
		$mail->Body = 'Downloading customers ended with error:'.$error;
		if (!$mail->send()) {
			throw new \RuntimeException('Email sent failed:'.$mail->ErrorInfo);
		}
	}

	/**
	 * @throws \PHPMailer\PHPMailer\Exception
	 */
	public static function sendCustomers(string $customersFile): void
	{
		$mail = self::createEmail();
		$mail->Subject = 'Bol.com Customers';
		$mail->Body = 'Updated file with customers list is attached.';
		$mail->addAttachment($customersFile);
		if (!$mail->send()) {
			throw new \RuntimeException('Email sent failed:'.$mail->ErrorInfo);
		}
	}

	/**
	 * @throws \PHPMailer\PHPMailer\Exception
	 */
	private static function createEmail(): PHPMailer
	{
		if (!defined('EMAIL_TO') || !defined('EMAIL_FROM')) {
			throw new \RuntimeException('Setup email is invalid');
		}
		$mail = self::setupSMTP(new PHPMailer());
		$mail->From = EMAIL_FROM;
		$mail->FromName = "Bol.com Customers Downloader";
		if (is_array(EMAIL_TO)) {
			array_map(fn($to) => $mail->addAddress($to), EMAIL_TO);
		} elseif (is_string(EMAIL_TO)) {
			$mail->addAddress(EMAIL_TO);
		}
		return $mail;
	}

	private static function setupSMTP(PHPMailer $mail): PHPMailer
	{
//		$mail->SMTPDebug = 3;
		$mail->isSMTP();
		$mail->Host = SMTP_HOST;
		$mail->SMTPAuth = true;
		$mail->Username = SMTP_USER;
		$mail->Password = SMTP_PASS;
		$mail->SMTPSecure = SMTP_SECURE;
		$mail->Port = SMTP_PORT;
		return $mail;
	}
}