<?php
namespace App;

use PHPMailer\PHPMailer\PHPMailer;

final class Mailer
{
	/**
	 * @throws \PHPMailer\PHPMailer\Exception
	 */
	public static function sendError(string $error, array $emailsTo): void
	{
		$mail = self::createEmail($emailsTo);
		$mail->Subject = 'Bol.com Customers Downloader Error';
		$mail->Body = 'Downloading customers ended with error:'.$error;
		if (!$mail->send()) {
			throw new \RuntimeException('Email sent failed:'.$mail->ErrorInfo);
		}
	}

	/**
	 * @throws \PHPMailer\PHPMailer\Exception
	 */
	public static function sendCustomers(string $customersFile, array $emailsTo): void
	{
		$mail = self::createEmail($emailsTo);
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
	private static function createEmail(array $emailsTo): PHPMailer
	{
		if (!$emailsTo || !defined('EMAIL_FROM')) {
			throw new \RuntimeException('Setup email is invalid');
		}
		$mail = self::setupSMTP(new PHPMailer());
		$mail->From = EMAIL_FROM;
		$mail->FromName = "Bol.com Customers Downloader";
		array_map(function($to) use ($mail) {$mail->addAddress($to);}, $emailsTo);
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