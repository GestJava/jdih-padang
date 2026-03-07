/**
 * Chart Utilities for JDIH Dashboard
 * Contains helper functions for chart rendering
 */

// Dynamic Colors Function for Charts
function dynamicColors() {
	var r = Math.floor(Math.random() * 255);
	var g = Math.floor(Math.random() * 255);  
	var b = Math.floor(Math.random() * 255);
	return "rgb(" + r + "," + g + "," + b + ")";
}

// Generate array of dynamic colors
function generateDynamicColors(count) {
	var colors = [];
	for (var i = 0; i < count; i++) {
		colors.push(dynamicColors());
	}
	return colors;
}

// Predefined color palettes for consistency
var chartColorPalettes = {
	primary: [
		'rgb(13, 110, 253)',  // Bootstrap primary
		'rgb(25, 135, 84)',   // Bootstrap success  
		'rgb(255, 193, 7)',   // Bootstrap warning
		'rgb(220, 53, 69)',   // Bootstrap danger
		'rgb(13, 202, 240)',  // Bootstrap info
		'rgb(108, 117, 125)', // Bootstrap secondary
	],
	modern: [
		'rgb(99, 174, 206)',
		'rgb(251, 179, 66)', 
		'rgb(62, 185, 110)',
		'rgb(255, 99, 132)',
		'rgb(54, 162, 235)',
		'rgb(255, 205, 86)'
	],
	gradient: [
		'linear-gradient(135deg, #667eea 0%, #764ba2 100%)',
		'linear-gradient(135deg, #f093fb 0%, #f5576c 100%)',
		'linear-gradient(135deg, #4facfe 0%, #00f2fe 100%)', 
		'linear-gradient(135deg, #43e97b 0%, #38f9d7 100%)',
		'linear-gradient(135deg, #fa709a 0%, #fee140 100%)',
		'linear-gradient(135deg, #a8edea 0%, #fed6e3 100%)'
	]
};

// Get colors from palette
function getColorPalette(paletteName, count) {
	var palette = chartColorPalettes[paletteName] || chartColorPalettes.primary;
	var colors = [];
	
	for (var i = 0; i < count; i++) {
		colors.push(palette[i % palette.length]);
	}
	
	return colors;
} 