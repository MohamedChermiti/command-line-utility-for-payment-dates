<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Annotation\Route;

class PaymentDatesController extends AbstractController
{
    /**
     * @Route("/generate-payment-dates", name="app_generate_payment_dates")
     */
    public function generatePaymentDates(): BinaryFileResponse
    {
        $filename = $this->getParameter('kernel.project_dir') . '/public/payment_dates.csv';
        $this->generatePaymentDatesCsv($filename);

        $response = new BinaryFileResponse($filename);
        $response->setContentDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT, 'payment_dates.csv');

        return $response;
    }

    private function generatePaymentDatesCsv(string $filename): void
{
    $paymentDates = array();

    $currentYear = (int)date('Y');
    for ($month = 1; $month <= 12; $month++) {
        $monthName = date('F', mktime(0, 0, 0, $month, 1, $currentYear));
        $baseSalaryPaymentDate = date('Y-m-d', strtotime("last day of $monthName $currentYear"));
        $baseSalaryPaymentDate = $this->adjustToLastFridayIfWeekend($baseSalaryPaymentDate);
        $bonusPaymentDate = date('Y-m-d', strtotime("$currentYear-$month-15"));
        $bonusPaymentDate = $this->adjustToFirstWednesdayIfWeekend($bonusPaymentDate);

        $paymentDates[$monthName] = [
            'salary' => $baseSalaryPaymentDate,
            'bonus' => $bonusPaymentDate
        ];
    }

    // Generate CSV content
    $csvContent = "Month Name,Salary Payment Date,Bonus Payment Date\n";
    foreach ($paymentDates as $month => $dates) {
        $csvContent .= "$month,{$dates['salary']},{$dates['bonus']}\n";
    }

    // Store CSV content in a file
    file_put_contents($filename, $csvContent);
}

private function adjustToLastFridayIfWeekend($date)
{
    $dayOfWeek = date('w', strtotime($date));
    if ($dayOfWeek === '0' || $dayOfWeek === '6') {
        $lastFriday = date('Y-m-d', strtotime('last Friday', strtotime($date)));
        return $lastFriday;
    }
    return $date;
}

private function adjustToFirstWednesdayIfWeekend($date)
{
    $dayOfWeek = date('w', strtotime($date));
    if ($dayOfWeek === '0' || $dayOfWeek === '6') {
        $firstWednesday = date('Y-m-d', strtotime('next Wednesday', strtotime($date)));
        return $firstWednesday;
    }
    return $date;
}
}