<?php

$currentDate = new DateTime();
$currentYear = $currentDate->format('Y');
$endDate = new DateTime($currentYear . '-12-31');

// CSV file
$csvFilePath = 'payment_dates.csv';
$csvFile = fopen($csvFilePath, 'w');
fputcsv($csvFile, ['Month Name', 'Salary Payment Date', 'Bonus Payment Date']);

while ($currentDate <= $endDate) {
    $salaryDate = new DateTime($currentDate->format('Y-m-t'));
    if ($salaryDate->format('N') >= 6) { // 6 represents Saturday, 7 represents Sunday
        $salaryDate->modify('last Friday');
    }
    
    $bonusDate = new DateTime($currentDate->format('Y-m-15'));
    if ($bonusDate->format('N') >= 6) {
        $bonusDate->modify('next Wednesday');
    }
    
    // Write to the CSV file
    fputcsv($csvFile, [
        $currentDate->format('F'),
        $salaryDate->format('Y-m-d'),
        $bonusDate->format('Y-m-d')
    ]);
    
    $currentDate->modify('next month');
}

fclose($csvFile);
echo "Payment dates generated in $csvFilePath.";
?>