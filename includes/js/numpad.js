var numField = "";

function numPad(num) {
if(numField.length<8){
	numField = numField+num;
	}
	document.idNum.idField.value = numField;
	
}

function clearNumPad() {
	numField = "";
	document.idNum.idField.value = numField;
	
}

function star() {
	numField = "UNKNOWNS";
	document.idNum.idField.value = numField;
	
}

function backspace() {
	newLength = (numField.length-1);
	numField = numField.substring(0,newLength);
	document.idNum.idField.value = numField;
}
