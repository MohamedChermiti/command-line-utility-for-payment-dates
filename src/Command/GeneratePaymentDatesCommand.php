<?php

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class GeneratePaymentDatesCommand extends Command
{
    protected static $defaultName = 'app:generate-payment-dates';

    private $projectDirectory;

    public function __construct(ParameterBagInterface $parameterBag, string $name = null)
    {
        $this->projectDirectory = $parameterBag->get('kernel.project_dir');
        parent::__construct($name);
    }

    protected function configure(): void
    {
        $this->setDescription('Generate payment dates for the sales department');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->generatePaymentDates($output);

        return Command::SUCCESS;
    }

    private function generatePaymentDates(OutputInterface $output): void
{
    $paymentDates = array();
    $currentYear = (int) date('Y');

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

    // Generate CSV 
    $csvContent = "Month Name,Salary Payment Date,Bonus Payment Date\n";
    foreach ($paymentDates as $month => $dates) {
        $csvContent .= "$month,{$dates['salary']},{$dates['bonus']}\n";
    }

    // Generate CSV file
    $filename = $this->projectDirectory . '/payment_dates.csv';
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