var MY_ID = "pinescashdrawer@hunter.perrin";
var em = Components.classes["@mozilla.org/extensions/manager;1"].
	getService(Components.interfaces.nsIExtensionManager);
// the path may use forward slash ("/") as the delimiter
// returns nsIFile for the extension's drawer program
var drawer_program = em.getInstallLocation(MY_ID).getItemFile(MY_ID, "drawer");

function drawer_run(e, kick) {
	// create an nsIProcess
	var process = Components.classes["@mozilla.org/process/util;1"]
		.createInstance(Components.interfaces.nsIProcess);
	process.init(drawer_program);
	
	// Run the process.
	// If first param is true, calling thread will be blocked until
	// called process terminates.
	// Second and third params are used to pass command-line arguments
	// to the process.
	var args = kick ? ["-k"] : ["-s"];
	process.run(true, args, args.length);
	
	// Dispatch the event.
	var evt = document.createEvent("Events");
	var result;
	switch (process.exitValue) {
		case 0:
			// The drawer program does not support the correct return codes.
			result = "pines_cash_drawer_not_supported";
			break;
		case 1:
			// There was an error with the drawer.
			result = "pines_cash_drawer_error";
			break;
		case 2:
			// The drawer is closed.
			result = "pines_cash_drawer_is_closed";
			break;
		case 3:
			// The drawer is open.
			result = "pines_cash_drawer_is_open";
			break;
		case 4:
			// The drawer was not found.
			result = "pines_cash_drawer_not_found";
			break;
		default:
			// The drawer program is misconfigured or not installed.
			result = "pines_cash_drawer_misconfigured";
			break;
	}
	evt.initEvent(result, true, false);
	e.target.dispatchEvent(evt);
}

document.addEventListener("pines_cash_drawer_check", function(e) {
	drawer_run(e, false);
}, false, true);

document.addEventListener("pines_cash_drawer_open", function(e) {
	drawer_run(e, true);
}, false, true);
