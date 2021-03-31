# BindAll: Binding Keyboard Shortcuts Productively


## Motivation

BindAll registers with the system keyboard shortcuts.
When a shortcut is pressed, it is sent to a PHP script 
where the user can conveniently attach actions to each
shortcut. BindAll makes the process of adding, updating,
and organizing one's shortcuts as efficient as possible.

The BindAll implementation could easily be ported to another 
language, e.g., Python. Feel free to contribute.


## Configuration

### Install dependencies

BindAll leverages `xbindkeys` for registering shortcuts
on the system, and is implemented in `php`.

```
   sudo apt-get install xbindkeys php-cli
```

Some interesting scripting commands leverage additional tools:

```
   sudo apt-get install xdotool libxrandr
```

### Register xbindkeys for autostart

Execute:

```
   gnome-session-properties &

```
Then click on "add", fill both "name" and "command" with the word `xbindkeys`.

To launch `xbindkeys` before restarting your session, you can type 
`nohup xbindkeys` in a terminal (ignore the warning message).

### Create you own binding files

```
   cd bindall
   cp bindall_template.php bindall.php
```

Note that you can move your `bindall.php` file anywhere you like.
It is strongly encouraged to version control this file, or at least
create regular backups for it.

### Create the generic xbindkeys binding files

In the folder where `bindall.php` is located, type:

```
   php bindall.php install
```

You can type `ls ~/.xbindkeysrc` to check that the file was created successfully.

### Test bindings

Type the shortcut `Win+c`, it should open gnome's calculator.


### Customize the bindings

Alternatively, edit your binding file:

```
   gedit bindall.php &    # or any other editor
```

To begin with, register the name of your favorite editor.
Afterwards, use `Win+Ctrl+K` to edit your *K*eybings.

Then, follow the examples from the template to devise your own binding.

## Suggested convention

Here is one possible way to recall bindings.

   - use `Win+x` for an application whose name starts with letter `x`.
   - use `Win+Alt+...`  for a website whose name starts with letter `x`.
   - use `Win+Ctrl+...` bindings for opening a frequently-used file.
   - use `Win+Ctrl+Alt+...` bindings for executing system configuration operation.
   - add `...+Shift+...` if the desired binding is already reserved.
   - use non-letter keys, e.g. `Win+@', for typing your email.

If a binding is already reserved by the system or the currently-running application,
then xbindkeys would have smaller priority.


## Supported keyboards

Currently, bindings on non-letter characters are supported only for US keyboard 
and French keyboard. The keyboard layout is automatically detected by the script.
Other layouts can be easily added, see the comments near the end of the file.


## Working

Bindall generates a configuration file, named `~/.xbindkeysrc`, for `xbindkeys`.
If `bindall.php` is located in `/home/charguer/conf/bindall/`, the file contains
one binding for every possible shortcut. Its first few lines are:
```
"php /home/charguer/conf/bindall/bindall.php action Mod4+a"
Mod4+a

"php /home/charguer/conf/bindall/bindall.php action Mod4+b"
Mod4+b
```
When they user type the shortcut `Mod4+a` (windows key and letter 'a'),
`xbindkeys` runs the commands
`php /home/charguer/conf/bindall/bindall.php action Mod4+a`.

The `bindall.php` script receives the key binding `Mod4+a`, and perform
the action registered by the user for this binding.


## Troubleshooting

   1. Execute `php bindall.php` to check that the files executes without error.
   2. Check that `~/.xbindkeysrc` exists and contains call to bindall.php for 
      every shortcut. Take the first line from that file, without the quotes,
      and try running it in a terminal.
   3. After typing a shortcut, the file `_last.sh` generated next to `bindall.php` 
      should contain the name of that shortcut. Check its contents, then type './_last.sh' 
      to try running it.
   4. After typing a shortcut, the file `_cmd.sh` generated next to `bindall.php` 
      should contain the command executed for that shortcut. Check its contents, then
      type './_cmd.sh' to try running it.
