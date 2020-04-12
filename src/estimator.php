<?php
/**
	{
		"region" : {
			"name": "Africa",
			"avgAge": 19.7,
			"avgDailyIncomeInUSD": 4,
			"avgDailyIncomePopulation": 0.73
		},
		"periodType": "days",
		"timeToElapse": 38,
		"reportedCases": 2747,
		"population": 92931687,
		"totalHospitalBeds": 678874
	}
*/

function covid19ImpactEstimator($data) {
	# Constants ...
	$reportedCasesMultiplier = 10;
	$severeCasesMultiplier = 50;
	$availableBedsMultiplier = 0.35;
	$severCasesByTimeMultiplier = 0.15;
	$icuCasesMultiplier = 0.05;
	$ventCasesMultiplier = 0.02;

	$timeElapse = 0;
	switch (strtolower($data['periodType'])){
		case 'days':
			$timeElapse = floor($data['timeToElapse']/3);
		break;
		case 'weeks':
			$timeElapse = floor( ($data['timeToElapse'] * 7) /3 );
		break;
		case 'months':
			$timeElapse = floor ( ($data['timeToElapse'] * 30) /3 );
		break;
		default:
			$timeElapse = floor ($data['timeToElapse']/3);
	}

	# Challenge 1.
	$impact = [];
	$severeImpact = [];

	$impact['currentlyInfected'] = floor($data['reportedCases'] * $reportedCasesMultiplier);
	$severeImpact['currentlyInfected'] = floor($data['reportedCases'] * $severeCasesMultiplier );	

	$impact['infectionsByRequestedTime'] = floor($impact['currentlyInfected'] * (2 ** $timeElapse) );
	$severeImpact['infectionsByRequestedTime'] = floor($severeImpact['currentlyInfected'] * (2 ** $timeElapse) );


	# Challenge 2.
	$impact['severeCasesByRequestedTime'] = floor($severCasesByTimeMultiplier * $impact['infectionsByRequestedTime'] );
	$severeImpact['severeCasesByRequestedTime'] = floor($severCasesByTimeMultiplier * $severeImpact['infectionsByRequestedTime'] );

	$availableBeds = $availableBedsMultiplier * $data['totalHospitalBeds'];
	$impact['hospitalBedsByRequestedTime'] = floor($availableBeds - $impact['severeCasesByRequestedTime'] ); 
	$severeImpact['hospitalBedsByRequestedTime'] = floor($availableBeds - $severeImpact['severeCasesByRequestedTime'] );


	# Challenge 3.
	$impact['casesForICUByRequestedTime'] = floor($icuCasesMultiplier * $impact['infectionsByRequestedTime'] );
	$severeImpact['casesForICUByRequestedTime'] = floor($icuCasesMultiplier * $severeImpact['infectionsByRequestedTime'] );

	$impact['casesForVentilatorsByRequestedTime'] = floor($ventCasesMultiplier * $impact['infectionsByRequestedTime'] );
	$severeImpact['casesForVentilatorsByRequestedTime'] = floor($ventCasesMultiplier * $severeImpact['infectionsByRequestedTime'] );

	$dollarsInFlightB4Div = $impact['infectionsByRequestedTime'] * $data['region']['avgDailyIncomePopulation'] * $data['region']['avgDailyIncomeInUSD'];
	$impact['dollarsInFlight'] =  floor( $dollarsInFlightB4Div / $data['timeToElapse']  );

	$dollarsInFlightB4Div = $severeImpact['infectionsByRequestedTime'] * $data['region']['avgDailyIncomePopulation'] * $data['region']['avgDailyIncomeInUSD'];
	$severeImpact['dollarsInFlight'] = floor( $dollarsInFlightB4Div / $data['timeToElapse'] );


  	return [
		'data' =>  $data, // the input data you got
		'impact' => $impact,  // your best case estimation
		'severeImpact' => $severeImpact // your severe case estimation
	];
}