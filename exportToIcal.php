<?PHP
/******************************************************************************
 * Simple and easy to implement template for exporting schedules in iCalendar *
 * Standard format using PHP.                                                 *
 * This template is set to events. Will be adding suport to all kinds of      *
 * calendar component specifications ASAP.                                    *
 * If you don't use POSTGRESQL as you DB, change the query functions to match *
 * the one you use.                                                           *
 * For more info on iCalendar Standard, go to: http://icalendar.org           *
 * Also: https://en.wikipedia.org/wiki/ICalendar
 * The use of this template is your responsability.                           *    
 * Thank you for using it.
 *                                                     *                                   
 * @author: Gustavo H. Leal                                                   *
 *                                                                            *
 ******************************************************************************/


///////////////////////////////////////// GET treatment to avoid code injection
 
  $hash = addslashes($_GET['hash']); // Hash argument. Add slashes to avoid SQL injection;
  $calendarID = intval($_GET['id']); // Reference your calendar.

///////////////////////////////////////////////////////////////// POST treatment
///////////////////////////////////////////////////////////////////// Header
  /** 
   *  set correct content-type-header
   * @param string $filename => name of the file that will be generate by the 
   * template;
   */
  $filename = "YouriCalFile";
  header("Content-type: text/calendar; charset=utf-8");
  header("Content-Disposition: in line; Filename=".$filename.".ics");
  /**
   * Header settings.
   * Page header, include files, permissions, etc.
   */
  $headerTitle = "Export to iCal";
  $myPATH = ini_get('include_path') . ':./include:../include:../../include';
  ini_set('include_path', $myPATH);

////////////////////////////////////////////////Check requester's permissions
/**
 *  You can send a hash through GET to check the user's permissions.
 *  The GET argument is set to 'hash'.
 *  If you don't want to verify user's permissions, just remove the if statments
 *  and the SQL request.
 *  @param string $hash      => user's hash, receive through GET;
 *  @param string $queryHash => SQL query to check who request the file;
 *  @param array    $isUser    => Response from DB with user's information;
 */
  if(isset($hash) && !empty($hash)){
    $queryHash = "Insira aqui sua query de verificação";
    $resultHash = pg_exec($conn, $queryHash);
    $isUser = pg_fetch_all($resultHash);
    if ($isUser){
      // SQL query to receive info about the subject of your calendar. You must have
      // a reference to be used as PRODID.
      $queryCalendar = "Insert here your query";
      // SQL query to receive the list of events.
      $queryEvents = "Insert here the query to your events" .
      

      $resultRecurso = pg_exec ($conn, $queryCalendar);
      $resultAgenda = pg_exec ($conn, $queryEvents);
      $calendar = pg_fetch_row ($resultCalendar);
      $events = pg_fetch_all ($resultAgenda);
      $locationID = $calendar['YourLocationReference'];
      $queryLocal = "Insert here the query for the location";
      $resultLocal = pg_exec ($conn, $queryLocal);
      $location = pg_fetch_row($resultLocal);
      
      //Remove all spaces and dash from the prod_id.
      $summary = $calendar['YourCalendarName'];
      $prod_id = preg_replace('/ /', '', $summary);
      $prod_id = preg_replace('/-/', '', $prod_id);
      //$prod_id = preg_replace('/ /', '', $prod_id);

      ///////////////////////////////////////////////////////////// BEGIN CALENDAR
      echo "BEGIN:VCALENDAR"."\r\n";
      echo "PRODID: ".$prod_id."@pucrs.br"."\r\n";
      echo "VERSION:2.0"."\r\n"; //Set as iCalendar format

      foreach($events as $event){
          /////////////////////////////////////////////GENERATE DTSTART, DTSTAMP E DTEND
          $date = $event['DATE'];
          //////////////DTSTART e DTSTAMP
          $dts = $date; // Start date
          $dts = preg_replace('/-/', '', $dts);
          $dts = preg_replace('/ /', 'T', $dts);
          $dts = preg_replace('/:/', '', $dts);

          /////////////////////////////////////////////////////////////////////DTEND
          $dte =strtotime($data); //End date
          $dth = ($event['DATE'] * 3600);
          $dte = ($dte+$dth);
          $dte = date('Ymd His', $dte);
          $dte = preg_replace('/ /', 'T', $dte);

          ///////////////////////////////////////////////////////////////DESCRIPTION
          $descript = $event['DESCRIPTION'];

          ////////////////////////////////////////////////////////////////////// UID
          // Generate a MD5 hash concatenating two infos from your event.
          // Each event must have a unique UID, otherwise will not be add to your calendar
          // when you import it.
          $uid = md5($event['REFERENCE'].$event['ID']);

          //////////////////////////////////////////////////// PRINT EVENT SINTAX
                echo "CALSCALE:GREGORIAN"."\r\n";
                echo "BEGIN:VEVENT"."\r\n";
                echo "SUMMARY: ".$summary."\r\n";
                echo "DTSTART: ".$dts."Z\r\n";
                echo "DTEND: ".$dte."Z\r\n";
                echo "DTSTAMP: ".$dts."Z\r\n";
                echo "UID: ".$uid;
                echo "LOCATION: ".$location[0]."\r\n";
                echo "DESCRIPTION: ".$descript."\r\n";
                echo "STATUS:CONFIRMED"."\r\n";
                echo "SEQUENCE:3"."\r\n";
                echo "END:VEVENT\r\n";

      }
      /////////////////////////////////////////////////////////END CALENDAR
      echo "END:VCALENDAR";
    } else {
      echo "Access denied!";
    } 
  } else {
    echo "Access denied
    !";
  }
?>
