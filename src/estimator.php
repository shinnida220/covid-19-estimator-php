<?php
/**
	{
		region: {
			name: "Africa",
			avgAge: 19.7,
			avgDailyIncomeInUSD: 5,
			avgDailyIncomePopulation: 0.71
		},
		periodType: "days",
		timeToElapse: 58,
		reportedCases: 674,
		population: 66622705,
		totalHospitalBeds: 1380614
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

	# Challenge 1.
	$impact = [];
	$severeImpact = [];

	$impact['currentlyInfected'] = floor($data['reportedCases'] * $reportedCasesMultiplier);
	$severeImpact['currentlyInfected'] = floor($impact['currentlyInfected'] * $severeCasesMultiplier );	

	$impact['infectionsByRequestedTime'] = floor($impact['currentlyInfected'] * (2 ** ($data['timeToElapse']/3) ) );
	$severeImpact['infectionsByRequestedTime'] = floor($severeImpact['currentlyInfected'] * (2 ** ($data['timeToElapse']/3) ) );


	# Challenge 2.
	$impact['severeCasesByRequestedTime'] = floor($severCasesByTimeMultiplier * $impact['infectionsByRequestedTime'] );
	$severeImpact['severeCasesByRequestedTime'] = floor($severCasesByTimeMultiplier * $severeImpact['infectionsByRequestedTime'] );

	$availableBeds = floor($availableBedsMultiplier * $data['totalHospitalBeds'] );
	$impact['hospitalBedsByRequestedTime'] = floor($availableBeds - $impact['severeCasesByRequestedTime'] ); 
	$severeImpact['hospitalBedsByRequestedTime'] = floor($availableBeds - $severeImpact['severeCasesByRequestedTime'] );


	# Challenge 3.
	$impact['casesForICUByRequestedTime'] = floor($icuCasesMultiplier * $impact['infectionsByRequestedTime'] );
	$severeImpact['casesForICUByRequestedTime'] = floor($icuCasesMultiplier * $severeImpact['infectionsByRequestedTime'] );

	$impact['casesForVentilatorsByRequestedTime'] = floor($ventCasesMultiplier * $impact['infectionsByRequestedTime'] );
	$severeImpact['casesForVentilatorsByRequestedTime'] = floor($ventCasesMultiplier * $severeImpact['infectionsByRequestedTime'] );

	$impact['dollarsInFlight'] =  floor( ($impact['infectionsByRequestedTime'] * $data['region']['avgDailyIncomePopulation'] * $data['region']['avgDailyIncomeInUSD']) / $data['timeToElapse'] );
	$severeImpact['dollarsInFlight'] = floor( ($severeImpact['infectionsByRequestedTime'] * $data['region']['avgDailyIncomePopulation'] * $data['region']['avgDailyIncomeInUSD']) / $data['timeToElapse'] );


  	return [
		'data' =>  $data, // the input data you got
		'impact' => $impact,  // your best case estimation
		'severeImpact' => $severeImpact // your severe case estimation
	];
}