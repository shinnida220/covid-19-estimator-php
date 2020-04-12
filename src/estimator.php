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
	$actualDays = $data['timeToElapse'];
	switch (strtolower($data['periodType'])){
		case 'weeks':
			$actualDays = $data['timeToElapse'] * 7;
		break;
		case 'months':
			$actualDays = $data['timeToElapse'] * 30;
		break;
		case 'days':
		default:
			$actualDays = $data['timeToElapse'];
	}

	$timeElapse = floor ($actualDays/3);

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

	$availableBeds = ceil($availableBedsMultiplier * $data['totalHospitalBeds']);
	$impact['hospitalBedsByRequestedTime'] = floor($availableBeds - $impact['severeCasesByRequestedTime'] ); 
	$severeImpact['hospitalBedsByRequestedTime'] = floor($availableBeds - $severeImpact['severeCasesByRequestedTime'] );
	
	// if(strtolower($data['periodType']) == "months"){
	// 	$impact['hospitalBedsByRequestedTime'] += 1; 
	// 	$severeImpact['hospitalBedsByRequestedTime'] += 1;
	// }


	# Challenge 3.
	$impact['casesForICUByRequestedTime'] = floor($icuCasesMultiplier * $impact['infectionsByRequestedTime'] );
	$severeImpact['casesForICUByRequestedTime'] = floor($icuCasesMultiplier * $severeImpact['infectionsByRequestedTime'] );

	$impact['casesForVentilatorsByRequestedTime'] = floor($ventCasesMultiplier * $impact['infectionsByRequestedTime'] );
	$severeImpact['casesForVentilatorsByRequestedTime'] = floor($ventCasesMultiplier * $severeImpact['infectionsByRequestedTime'] );

	$dollarsInFlightB4Div = $impact['infectionsByRequestedTime'] * $data['region']['avgDailyIncomePopulation'] * $data['region']['avgDailyIncomeInUSD'];
	$impact['dollarsInFlight'] =  floor( $dollarsInFlightB4Div / $actualDays  );

	$dollarsInFlightB4Div = $severeImpact['infectionsByRequestedTime'] * $data['region']['avgDailyIncomePopulation'] * $data['region']['avgDailyIncomeInUSD'];
	$severeImpact['dollarsInFlight'] = floor( $dollarsInFlightB4Div / $actualDays );


  	return [
		'data' =>  $data, // the input data you got
		'impact' => $impact,  // your best case estimation
		'severeImpact' => $severeImpact // your severe case estimation
	];
}