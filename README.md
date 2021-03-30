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
   - use `Win+Shift+x` if the binding is already reserved by the system or another app.
   - use `Win+Alt+x`  for a website whose name starts with letter `x`.
   - use `Win+Alt+Shift+x`  if you have another one that starts with the same letter.
   - use `Win+Ctrl+...` bindings for executing your favorite scripts.
   - use `Win+Ctrl+Alt+...` bindings for executing system configuration operations.

If a binding is already reserved by the system or the currently-running application,
then xbindkeys would have smaller priority.

## Troubleshooting

   1. Execute `php bindall.php` to check that the files executes without error.
   2. Check that `~/.xbindkeysrc` exists and contains call to bindall.php for every shortcut. 
   3. After typing a shortcut, the file `_last.txt` generated next to `bindall.php` 
      should contain the name of that shortcut.
   4. After typing a shortcut, the file `_cmd.txt` generated next to `bindall.php` 
      should contain the command executed for that shortcut.
   5. Execute `chmod +x _cmd.txt && echo ./_cmd.txt | bash` in a terminal to re-run 
      the requested command and see its potential errors.
