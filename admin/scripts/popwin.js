var win = null;
function popup(locatie,nume,w,h){
	LeftPosition = (screen.width) ? (screen.width-w)/2 : 0;
	TopPosition = (screen.height) ? (screen.height-h)/2 : 0;
	settings = 'height='+h+',width='+w+',top='+TopPosition+',left='+LeftPosition+',scrollbars=0, resizable=0, status=0;';
	fereastra = window.open(locatie,nume,settings);
}