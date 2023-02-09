
/*

This file is part of Primitive Point of Sale.

    Primitive Point of Sale is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    Primitive Point of Sale is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with Primitive Point of Sale.  If not, see <http://www.gnu.org/licenses/>.

*/



// this is called during autocomplete selects with the enter key...need to find a way 
// to avoid this, since this function will be needed if a barcode scanner is hooked up
export function check_enter(sku, evt)
{
	if(evt.key == 'Enter')			//alert(evt.target);
		lookup_item();
	
		return false;
}



export function show_note(title='Alert', msg, type='info')
{
    Swal.fire(title, msg, type)

}



export function update_clock()
{

  var currentTime = new Date();

  var currentHours = currentTime.getHours();
  var currentMinutes = currentTime.getMinutes();
  var currentSeconds = currentTime.getSeconds();

  var currentYear = currentTime.getFullYear();
  var currentMonth = currentTime.getMonth() + 1;
  var currentDay = currentTime.getDate();

  var currentDayName = currentTime.getDay();

  var day_of_week = new Array('Sun','Mon','Tue','Wed','Thurs','Fri','Sat');
  var mo_of_year = new Array('Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sept','Oct','Nov','Dec');


  // Pad the minutes and seconds with leading zeros, if required
  currentMinutes = (currentMinutes < 10 ? "0" : "") + currentMinutes;
  currentSeconds = (currentSeconds < 10 ? "0" : "") + currentSeconds;
  currentMonth = (currentMonth < 10 ? "0" : "") + currentMonth;

  var timeOfDay = (currentHours < 12) ? "am" : "pm";

  currentHours = (currentHours > 12) ? currentHours - 12 : currentHours;

  currentHours = (currentHours == 0) ? 12 : currentHours;

  // store in global variables for the payments screen
   $clock.currentHours = currentHours;
   $clock.currentMinutes = currentMinutes;
   $clock.ampm = timeOfDay;

  // Compose the string for display
  var currentTimeString = currentHours + ":" + currentMinutes + " " + timeOfDay;
  var currentDateString =  day_of_week[currentDayName] + ' ' + mo_of_year[currentMonth-1] + " " + currentDay + ", "+ currentYear;

  // Update the time display

	$clock.container.html(currentDateString + ' &nbsp; &nbsp;' + currentTimeString);
	
	window.setTimeout("update_clock()", 60000);
	
}

export function chgView(activeDiv)
{
	let views = [$pos.customer_dialog, $billing.dialog, $catalog.dialog, $pos.mainContainer]
	views.forEach(v => v.hide())

	activeDiv.show()

}