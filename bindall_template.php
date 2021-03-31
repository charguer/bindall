<?php

/*
 * BindAll -- https://github.com/charguer/bindall
 */

$myeditor = "gedit";
$mybrowser = "chromium-browser";
$myexplorer = "nautilus";

$log_commands = true; // generate _last.sh and _cmd.sh to save the commands

// Warning: beware that the generation of certain commands may require
// `escapeshellarg(s)` to escape the special characters in the string `s`.




/*************************************************************************************/
// Codes for special characters

$supported_languages = array('fr', 'us');

// Language-independent special keys.
$special_keys = array('Escape', 'Print', 'Scroll_Lock', 'KP_Subtract', 'Insert', 'Home', 'Prior', 'Delete', 'End', 'Next', 'BackSpace', 'Tab', 'Return', 'space', 'Left', 'Right', 'Up', 'Down');
// Note: 'Next' means PageUp, and 'Prior' means PageDown.

// US keyboard
$number_us = array(1, 2, 3, 4, 5, 6, 7, 8, 9, 0);
$special_us = array('grave', 'minus', 'equal', 'bracketleft', 'bracketright', 'apostrophe', 'backslash', 'comma', 'period', 'slash');

// French keyboard
$number_fr = array(
   '1' => 'ampersand',
   '2' => 'eacute',
   '3' => 'quotedbl',
   '4' => 'apostrophe',
   '5' => 'parenleft',
   '6' => 'minus',
   '7' => 'egrave',
   '8' => 'underscore',
   '9' => 'ccedilla',
   '0' => 'agrave');
$special_fr = array('twosuperior', 'parenright', 'equal', 'dead_circumflex', 'dollar', 'ugrave', 'asterisk', 'less', 'comma', 'semicolon', 'colon', 'exclam');



/*************************************************************************************/
// Helper functions for implementing actions

// Compute the path the present script

function path_to_current_script() {
   global $argv;
   return realpath($argv[0]);
}

// Function for generating a log file, like _last.sh or _cmd.sh
function generate_log_file($name, $contents) {
   $filepath = dirname(path_to_current_script()).'/'.$name;
   file_put_contents($filepath, $contents);
   shell_exec("chmod +x ".$filepath);
}

// Log the command that was requested

if ($log_commands) {
   generate_log_file('_last.sh', "php ".implode(' ', $argv)."\n");
}

// Function to raise an error

function error($msg) {
   echo $msg."\n";
   exit(1);
}

// Get an absolute path without leading '~' symbol

function expand_home($filename) {
   // Note: could probably be implemented using `pwd`
   if (! empty($filename) && $filename[0] == '~')
      $filename = exec('echo $HOME').'/'.substr($filename,1);
   return $filename;
}

// Get the local hostname

$myhostname = gethostname();

function host_name() {
   global $myhostname;
   return $myhostname;
}

// Command for editing a file using $myeditor

function cmd_edit($filename) {
   global $myeditor;
   return $myeditor.' "'.expand_home($filename).'"';
}

// Launch Wine on a given command

function wine_arg($cmd) {
   return "\"".str_replace('\\', '\\\\', $cmd)."\"";
}

function cmd_wine($cmd) {
   return "wine ".wine_arg($cmd);
}

// Launch explorer on a given path

function cmd_explorer($path) {
   global $myexplorer;
   return $myexplorer." ".$path;
}

// Launch browser on a given url

function cmd_browser($url) {
   global $mybrowser;
   return $mybrowser." \"".$url."\"";
}

function cmd_chromium($url) {
   return "chromium-browser \"$url\"";
}

function cmd_chromium_incognito($url) {
   return "chromium-browser --incognito \"$url\"";
}

function cmd_chrome($url) {
   return "google-chrome \"$url\"";
}

function cmd_chrome_incognito($url) {
   return "google-chrome --incognito \"$url\"";
}

// Control the output display
// Hint: use "xrandr --listactivemonitors" to get the names and resolutions of the monitors in use

function cmd_set_output_display($mode) {

   // If you have several machine sharing the same bindall.php, use
   //    if (host_name() == "myhostname")  to run the appropriate code.
   if ($mode == 'small')
      return "xrandr --output eDP-1 --mode 1920x1080 --output DP-2-1 --off";
   else if ($mode == 'big')
      return "xrandr --output eDP-1 --off --output DP-2-1 --mode 1920x1080";
   else if ($mode == 'dual-below')
      return "xrandr --output DP-2-1 --mode 1920x1080 --output eDP-1 --mode 1920x1080 --pos 0x1080";
   else if ($mode == 'dual-side')
      return "xrandr --output DP-2-1 --mode 1920x1080 --output eDP-1 --mode 1920x1080 --pos 1920x0";
   error("cmd_set_output_display invalid argument: $mode.");
}

// Command for putting the machine to sleep

function cmd_power($mode) {
   // To make this work, you first need to execute in a terminal "sudo visudo"
   // then paste at the end "nonprivilegeduser ALL=NOPASSWD:/usr/sbin/pm-suspend",
   // then exit and save (type CTRL+X, then type Y, then ENTER).
   // You may replace "nonprivilegeduser" by your login, for additional security.
   // For other modes: see http://manpages.ubuntu.com/manpages/hardy/man8/pm-hibernate.8.html
   if ($mode == 'suspend')
      return "sudo pm-suspend";
   error("cmd_power invalid argument: $mode.");
}

// Command for typing text

function cmd_type($txt) {
  return 'xdotool sleep 0.15; WID=$(xdotool getwindowfocus); xdotool windowactivate --sync ${WID} type "'.($txt).'"';
}

// Command for typing email -- a work-around is needed for the '@' character, it seems,
// yet the workaround is not perfect

function cmd_type_email($pre, $post) {
  return 'xdotool sleep 0.15; WID=$(xdotool getwindowfocus); xdotool windowactivate --sync ${WID} type "'.($pre).'"; xdotool windowactivate --sync ${WID} key U0040; xdotool windowactivate --sync ${WID} type "'.($post).'"';
}

// Commands for bringing to front specific kind of windows

function raise_windows($name, $options, $alt = '') {
   if ($alt != '') {
       $alt = " || " + $alt;
   }
   return "wmctrl $options -a \"$name\" $alt";
}

function raise_windows_class($name, $alt = '') {
   return raise_windows($name, "-x", $alt);
}

function raise_windows_name($name, $alt = '') {
   return raise_windows($name, "", $alt);
}

// Command for setting the size and position of the active window

function set_windows_geometry($x, $y, $w, $h) {
   return "xdotool getactivewindow windowmove $x $y; xdotool getactivewindow windowsize $w $h";
}

// Command for taking a screenshot of a particular area of the screen
// The file is saved in ~/Pictures, and is named according to the date.

function screenshot_area_to_file() {
   $folder = '~/Pictures';
   date_default_timezone_set('Europe/Paris');
   $timestp = date("Y_m_d_H_i_s");
   $filename = $folder."/shot_".$timestp.".png";
   return "gnome-terminal --geometry 80x3+0+0 -e \"bash -c \\\"gnome-screenshot -a -f ".$filename."\\\"\"";
}

function screenshot_area_to_clipboard() {
   return "gnome-terminal --geometry 80x3+0+0 -e \"bash -c \\\"gnome-screenshot -a -c\\\"\"";
}


/*************************************************************************************/
// Functions for binding shortcuts

// IMPORTANT: the keycodes masks must respect the order : Control Shift Alt Mod4,
// For example "Control+Alt+a" is valid but "Alt+Control+a" is not valid.

class Action
{
    public $keycode, $command;
    function __construct($keycode, $command) {
       $this->keycode = $keycode;
       $this->command = $command;
    }
}

// Generic function for binding shortcuts

function bind($keycode, $cmd) {
   return new Action($keycode, $cmd);
}

// Specialized functions, following the convention for organizing shortcuts

function web1($letter, $url) {
   return bind('Alt+Mod4+'.$letter, cmd_browser($url));
}

function web2($letter, $url) {
   return bind('Shift+Alt+Mod4+'.$letter, cmd_browser($url));
}

function app1($letter, $cmd) {
   return bind('Mod4+'.$letter, $cmd);
}

function app2($letter, $cmd) {
   return bind('Shift+Mod4+'.$letter, $cmd);
}

function fil1($letter, $cmd) {
   return bind('Control+Mod4+'.$letter, $cmd);
}

function fil2($letter, $cmd) {
   return bind('Control+Shift+Mod4+'.$letter, $cmd);
}

function sys1($letter, $cmd) {
   return bind('Control+Alt+Mod4+'.$letter, $cmd);
}

function sys2($letter, $cmd) {
   return bind('Control+Shift+Alt+Mod4+'.$letter, $cmd);
}

/*************************************************************************************/
// Bindings

function get_actions() {
   // Direct access to the global variables that give special key codes
   global $number_fr, $special_fr, $special_keys, $number_us, $special_us;

   return array(

   web1('n', "http://www.nytimes.com"),
   web1('g', "https://www.gmail.com/"),

   app1('c', "gnome-calculator"),
   app2('c', "unity-control-center"),
   app1('d', cmd_browser("https://drive.google.com/")),
   app1('e', cmd_explorer("~")), // 'e' like 'explorer'
   app1('g', "gedit"),
   app1('i', "libreoffice --impress"),
   app1('n', cmd_browser("https://netflix.com/")),
   app1('s', "skypeforlinux"),
   app1('t', "gnome-terminal --working-directory=~"),
   app2('t', raise_windows_class("Terminal")), // `Win+1` brings terminal window to front
   app1('w', "chromium-browser"), // 'w' like 'web'
   app2('w', "google-chrome"), // w like web
   app1('x', "thunderbird"), // 'x' like 'exchange' and because it's close to the Win key
   app1($number_fr[1], screenshot_area_to_clipboard()), // Win+1 for screenshot of area saved to clipboard
   app2($number_fr[1], screenshot_area_to_file()), // Win+Shift+1 for screenshot of area saved to ~/Pictures

   fil1('b', cmd_edit(path_to_current_script())),
   fil2('b', cmd_edit("~/.bashrc")),
   fil1('w', cmd_explorer("~/work")),

   app1('semicolon', cmd_type('·')), // `Win+;` inserts a center dot character

   app1($number_fr[0], cmd_type_email("first.last", "gmail.com")), // `Win+@` inserts email

   sys1('p', // Ctrl+Win+Alt+p places the current window at a given position on the screen,
             // depending on the hostname of the machine
      (host_name() == "mymachine")
      ? set_windows_geometry(300, 0, 1100, 1080)
      : set_windows_geometry(600, 0, 1300, 1600)),

   sys1('s', cmd_set_output_display('small')), // needs to be configured, see cmd_set_output_display
   sys1('b', cmd_set_output_display('big')),
   sys1('d', cmd_set_output_display('dual-below')),
   sys2('d', cmd_set_output_display('dual-side')),

   );
}

/*************************************************************************************/
// Tools for running commands

function shell_exec_return_immediately($command) {
   $cmd = $command." > /dev/null 2>/dev/null &";
   shell_exec($cmd);
}

function find_action($keycode) {
   $actions = get_actions();
   foreach ($actions as $action)
      if ($action->keycode == $keycode)
         return $action;
   error("No action registered for $keycode.");
}

function execute_action($keycode) {
   global $log_commands;
   $action = find_action($keycode);
   if ($log_commands)
      generate_log_file('_cmd.sh', $action->command."\n");
   shell_exec_return_immediately($action->command);
}


/*************************************************************************************/
// Tools for installing shortcuts

function detect_keyboard_layout() {
   // Returns FALSE if keyboard layout is not supported
   global $supported_languages;
   $lines = array();
   exec('setxkbmap -query', $lines);
   $query = implode("\n", $lines);
   foreach ($supported_languages as $lang)
      if (preg_match('/layout:.*'.$lang.'.*/', $query))
         return $lang;
   return FALSE;
}

function get_all_special_keys() {
   // If the keyboard layout is not supported, bindings on non-letter characters are not available.
   global $special_keys, $number_fr, $special_fr, $number_us, $special_us;
   $lang = detect_keyboard_layout();
   $t = array();
   if ($lang == 'fr')
      $t = array_merge($number_fr, $special_fr);
   else if ($lang == 'us')
      $t = array_merge($number_us, $special_us);
   return array_merge($special_keys, $t);
}

function enumerate_all_keys($callback) {
   for ($i = 'a'; $i < 'z'; $i++)  // letter keys
      $callback($i);
   for ($i = '1'; $i < '12'; $i++)  // function keys
      $callback('F'.$i);
   foreach (get_all_special_keys() as $key) // special keys
      $callback($key);
}

function enumerate_all_shortcuts($callback) {
   $mod4 = 'Mod4+'; // forced to avoid conflicts with other apps
   foreach (array('','Control+') as $control) {
      foreach (array('','Shift+') as $shift) {
         foreach (array('','Alt+') as $alt) {
            $masks = $control.$shift.$alt.$mod4;
            enumerate_all_keys(function($key) use (&$callback, &$masks) {
                  $callback($masks.$key); });
         }
      }
   }
}

function install_bindings() {
   $entries = array();
   $path = path_to_current_script();
   enumerate_all_shortcuts(function($keycode) use (&$entries, &$path) {
      $entry = "\"php ".$path." action ".$keycode."\""."\n".$keycode."\n\n";
      $entries[$keycode] = $entry;
   });
   $contents = implode('', $entries);
   $path = expand_home('~/.xbindkeysrc');
   file_put_contents($path, $contents);
   exec('killall -q xbindkeys; xbindkeys'); // restart xbindkeys
}


/*************************************************************************************/
// Main

// Main processing
$args = $argv;
array_shift($args);
if (empty($args)) {
   error("No arguments provided.");
} else {
   $cmd = array_shift($args);
   if ($cmd == 'action') {
      if (empty($args)) {
         error("No keycode provided for the action.");
      } else {
         $keycode = array_shift($args);
         execute_action($keycode);
      }
   } else if ($cmd == 'install') {
      install_bindings();
   } else {
      error("Invalid argument '$cmd'. Expected 'action' or 'install'");
   }
}



/*************************************************************************************/
// Additional documentation

/*
   Example keycode used by xbindkeys: "Control+Shift+a".

   Available meta keys: Control, Shift, Alt, Mod4.

   Note that `Mod4` is another name for `Super` or for the `Windows` key.

   Keyboard includes:
   - Normal keys: a b c etc..
   - Function keys: F1 F2 etc...
   - Non-letter characters: numbers, special characters
   - Special keys: Home etc...

   To obtain the list of key codes, use "xbindkeys -mk".
   For different keyboard layouts, first call, e.g., "setxkbmap fr" or "setxkbmap en".
   In the code, we use "setxkbmap -query" to find out the layout automatically.
*/

?>