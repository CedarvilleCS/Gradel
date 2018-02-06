function addReturnKey(str){	
	return removeReturnKey(str).replace(/\n/g, '\u21b5\n');
}

function removeReturnKey(str){
	return str.replace(/\u21b5/g, '');
}