<?php

// read employees.csv into employeeData
define('EMPLOYEE_DATA', 'employees.csv');

function main()
{
	// use PHP_SAPI to determine server environment
	if (PHP_SAPI === 'cli') {
		display(readEmployees(), calculate(readEmployees(), readTime()));
	} else {
		displayWeb(readEmployees(), calculate(readEmployees(), readTime()));
	}
}
main();

function readEmployees()
{
	$employeeDataFile = file(EMPLOYEE_DATA);
	/* var_dump($employeeDataFile); */

	$employeeData = [];

	foreach ($employeeDataFile as $value) {
		$employeeDataItem = explode(',', $value);
		array_push($employeeData, $employeeDataItem);
	}

	return $employeeData;
}

function readTime()
{
	// read in 2020 27.php into timeCard
	if (PHP_SAPI === 'cli') {
		echo "Enter Timecard Date" . PHP_EOL;
		$year = trim(readline("Year: "));
		$week = trim(readline("Week: "));

		echo PHP_EOL;

		if (glob($year . ' ' . $week . '*.csv')) {
			$timecards = glob($year . ' ' . $week . '.csv');
			/* var_dump($timecards); */
		} else {
			die('No Time Card Found' . PHP_EOL);
		}
	} else { ?>
		<!doctype html>

		<head>
			<title>Payroll Lab</title>
			<link href="style.css" rel="stylesheet">
		</head>

		<body>
			<header>
				<h1>Payroll Lab</h1>
			</header>
			<?php $timecards = glob('[0-9]*.csv'); ?>
			<div id="timecardDiv">
				<label for="timecard">Timecard</label>
				<select id="timecard">
					<?php foreach ($timecards as $filename) : ?>
						<option><?= $filename ?></option>
					<?php endforeach ?>
				</select>
			</div>

		<?php
	}

	foreach ($timecards as $filename) {
		$timecardFile = file($filename);
		$timecardData = [];
		foreach ($timecardFile as $value) {
			$timecardDataItem = explode(',', $value);
			array_push($timecardData, $timecardDataItem);
		}
	}

	return $timecardData;
}

function calculate($employeeData, $timecardData)
{
	// find a specific person by their id even in multiple places in the timecard to find out how many hours they worked (read each person's timecardData[2], add them up)

	// var_dump($timecardData) gives you index => timecardDataItem {[0] => id, [1] => timestamp, [2] => hours worked}
	// var_dump($employeeData) gives you index => employeeDataItem {[0] => id, [1] => name, [2] => pay rate}
	// $employeeDataItem: employee data array for each employee
	// $employeePay -> $idIndex: id used as index
	// $employeePay -> $pay: current amount of hours (will be multiplied by rate)
	// $employeeDataItem[0]: id
	// $employeeDataItem[1]: name
	// $employeeDataItem[2]: pay rate

	$employeePay = [];
	foreach ($timecardData as $timecardEntryIndex => $timecardEntry) {
		// convert hours from strings to floats for easy addition
		$timecardEntry[2] = floatval($timecardEntry[2]);
		// read this id
		if (!array_key_exists($timecardEntry[0], $employeePay)) {
			// if id doesn't exist as key, id becomes this key
			$employeePay[$timecardEntry[0]] = $timecardEntry[2];
			floatval($employeePay[$timecardEntry[0]]);
		} else {
			// else the id's hours get added to existing key
			$employeePay[$timecardEntry[0]] += $timecardEntry[2];
		}
	}

	foreach ($employeeData as $employeeDataItem) {
		foreach ($employeePay as $idIndex => $pay) {
			if ($employeeDataItem[0] == $idIndex) {
				$employeeDataItem[2] = floatval($employeeDataItem[2]);	// convert each employee's pay rate into float
				$employeePay[$idIndex] = $pay * $employeeDataItem[2];
			}
		}
	}
	return $employeePay;
}

function display($employeeData, $employeePay)
{
	// brings employeeData (name) and employeePay together, connected by id -- cli
	foreach ($employeeData as $employeeDataItem) {
		foreach ($employeePay as $idIndex => $pay) {
			if ($employeeDataItem[0] == $idIndex) {
				$employeeDataItem[2] = floatval($employeeDataItem[2]);	// convert each employee's pay rate into float
				echo "Name: $employeeDataItem[1]" . PHP_EOL;
				echo "Pay Rate: $$employeeDataItem[2]/hr" . PHP_EOL;
				$hours = $pay / $employeeDataItem[2];
				echo "Hours Worked: $hours" . PHP_EOL;
				echo "Pay: $$employeePay[$idIndex]" . PHP_EOL . PHP_EOL;
			}
		}
	}
}

function displayWeb($employeeData, $employeePay)
{ ?>
		<table id="employeePayTable">
			<tr>
				<th>Employee</th>
				<th>Weekly Pay</th>
			</tr>
			<?php foreach ($employeeData as $employeeDataItem) : ?>
				<?php foreach ($employeePay as $idIndex => $pay) : ?>
					<?php if ($employeeDataItem[0] == $idIndex) { ?>
						<tr>
							<td><?= $employeeDataItem[1] ?></td>
							<td>$<?= $employeePay[$idIndex] ?></td>
						</tr>
					<?php } ?>
				<?php endforeach ?>
			<?php endforeach ?>
		</table>
		</body>

		</html>
	<?php }

/*

Employee 1
Rate: 11.11
Hours: 1
Pay: 11.11

Employee 2
Rate: 22.22
Hours: 4
Pay: 88.88

Employee 3
Rate: 33.33
Hours: 9
Pay: 299.97

*/
