Vim + Unix == IDE
====

.fx: titleslide

---

Yes, I work for Zend
====

----

Yes, I use Vim
====

----

Why?
----

* **It's fast**

Presenter Notes
----

* Starting speed
* Speed of editing

----

Why?
----

* It's fast
* **It's ubiquitous**

Presenter Notes
----

* Installed and aliased as "vi" by default on most linux distributions
* Most mainframes will have it
* Cross platform -- available on Windows and Mac OSX, as well as others.
  * In fact, it was originally written for Amiga OS, for which I have a certain
    fondness as that was my first PC.
  * I can use the same editing environment on my desktop, my laptop, my virtual
    machines, and my servers.

----

Why?
----

* It's fast
* It's ubiquitous
* **Engineered for efficient text manipulation**

Presenter Notes
----

* As a developer, this is key
* As a writer, even simply of email, this is key
* I use it for _all_ text -- email, news posts, blog posts, coding, articles,
  books...

----

It follows the Unix Philosophy
----

> Write programs that do one thing and do it well. 
>
> Write programs to work together. 
>
> Write programs to handle text streams, because that is the universal interface.
>
> *-- Doug McIlroy, inventor of Unix pipes*

Presenter Notes
----
* Works with streams
* Does text manipulation only
* Offers a language for writing extensions, which typically do _one thing well_.

----

Vim Bootcamp
====

----

If you haven't already, use `vimtutor`
====

----

First things first: Modal editors
----

* Different execution modes, based on what you're trying to accomplish.
* In vim, typically: 
  * **Insert** mode (actual text entry), 
  * **Visual** mode (visual text selection), 
  * **Normal** mode (movement, commands)

----

Choosing modes: Insert mode
----

From Normal mode...

* `i` enters "Insert" mode, at the current position
* `I` enters "Insert" mode, at the first position of the line
* `a` enters "Insert" mode, *appending* the current position
* `A` enters "Insert" mode, *appending* the line
* `o` enters "Insert" mode, *opening* a new line beneath the current
* `O` enters "Insert" mode, *opening* a new line above the current
* `C` enters "Insert" mode, *changing* from current position to EOL
* `R` enters "Insert" mode, *replacing* text as you type

Presenter Notes
----
* "a", for "append"
* There are other ways as well; "r" for "replace" and "s" for "substitute"
* Demonstrate each

----

Choosing modes: Visual mode
----

From Normal mode...

* `v` enters "Visual" mode to highlight contiguous text ranges
* `V` enters "Visual" mode to highlight lines of text
* `Ctrl-v` enters "Visual" mode to highlight blocks of text

Presenter Notes
----
* Demonstrate each

----

Choosing modes: Normal mode
----

* From Insert or Visual mode, hitting `Escape` will return you to Normal mode

Presenter Notes
----
* When all else fails, hit Escape

----

Insert Mode
====

----

Basics
----

* Start typing
* If you need to move around, go back to Normal mode

Presenter Notes
----
* Normal mode offers the most efficient movement
* Demonstrate insert mode and returning to Normal mode

----

Normal Mode
====

----

A Note on Visual Mode
====

Presenter Notes
----
* Visual mode is for selecting text
* All movement keys shown and demonstrated for Normal mode are valid for Visual
  mode as well
* I'll be demonstrating this throughout

----

Basics: Movement
----

* Home row:
  * `h` - left
  * `j` - down
  * `k` - up
  * `l` - right
* `Ctrl-F` - page forward
* `Ctrl-B` - page backward
* `:he movement` for more information

Presenter Notes
----
* Highly efficient - you don't need to move your fingers from standard typing
  position in order to move around.
* *All movement keys work in Visual mode as well*
* Pro tip: map your caps lock key to Ctrl to make it easier to reach

----

Basics: Getting help
----

* `:he` invokes the help system
* `:he <topic>` invokes the help system with that topic
* `Ctrl-]` jumps to a tag
* `Ctrl-t` goes back to the screen from which you jumped

Presenter Notes
----

* Demonstrate this: ":he", ":he movement", jumping

----

Faster movement
----

* By word: `w`, `W`, `e`, `E`
* Start of line/End of line: `0`, `$`
* Start of document/End of document: `gg`, `G`
* To line number {N}: `{N}G`

Presenter Notes
----
* `:se number` turns on line numbers, which is useful for jumping around
* Demonstrate each of these

----

Faster movement: searching
----

* Search for next line with {searchpattern}: `/{searchpattern}`
* Search for previous line with {searchpattern}: `?{searchpattern}`
* Go to first occurence of {character} in this line: `f{character}`
* Go to previous occurence of {character} in this line: `F{character}`
* `t` and `T` are like `f` and `F`, but go to character preceding

Presenter Notes
----
* Demonstrate each of these
* Talk about the `\v` switch with searches, and how it enables PCRE regex

----

Faster movement: matching pairs
----

* Go to matching (brace, bracket, paren, quote): `%`

> When in doubt, parenthesize. At the very least it will let some poor schmuck
> bounce on the % key in *vi*.
>
> *-- Larry Wall*

Presenter Notes
----
* Demonstrate this in a code sample

----

Faster movement: marks
----

* Bookmark a location: `m{character}`
* Go to a bookmark: `'{character}`

Presenter Notes
----

* Demonstrate this
* Discuss some patterns of marks you've used/seen

----

Common commands
----

* Write/save a file: `:w<CR>`
* Quit: `:q`
* Write and quit: `:wq` or `ZZ`

Presenter Notes
----
* Demonstrate the above
* Also demonstrate what happens if you try to quit and the file is not saved

----

Cutting and pasting
----

* Copy/"Yank": `y` (`yy` to yank current line)
* Cut/"Delete": `d` (`dd` to delete current line)
* Cut/delete characters: `x` (current), `X` (previous)
* Paste: `p` (after current position), `P` (before)
* Undo: `u`

Presenter Notes
----
* Demonstrate each of the above
* Demonstrate `xp` (twiddling characters)

----

Replacing text
----

* Flip the case of the current character: `~`
* Replace the current selection: `r`
* Substitute matched text: `:s /{pattern}/{substitution}/{g}`

Presenter Notes
----
* `r` actually needs an explanation of visual mode
* `s` is like perl's `s//` operator
* As shown, only operates on current line

----

Faster operations: use quantifiers
----

* Move to the 4th word: `4w`
* Move to the 5th occurence of "x": `4fx`
* Yank the next six lines: `6yy`
* Delete to the end of the 3rd word: `d3e`

----

Specifying ranges
----

* Most commands allow *ranges* over which they should operate
* Typically, it will be what you highlight in Visual mode
* You can specify it manually
  * `:{start},{end} {command}`
  * `:%` -- all lines in file
  * `:'<,'>` -- between start and end of visual selection
  * `:3,15` -- from lines 3 to 15

Presenter Notes
----
* demonstrate substitution over whole, partial documents, and selection

----

Power search: perform commands
----

* Execute a command on lines matching a pattern: `:{range} g/{pattern}/{command}`
* Execute a command on lines NOT matching a pattern: `:{range} v/{pattern}/{command}`

Presenter Notes
----
* "v" == "inVerse"

----

Formatting text/code
----

* Format/wrap the current paragraph: `gqip`
* Format the current selection: `gq`
* More: `:he gq`

Presenter Notes
----
* Demonstrate with an email or blog post; show with selection as well.
* Mention that the rules are configurable, and particularly on a per-syntax
  basis

----

Piping
----

* "Read" a file into the current: `:r {filename}`
* Execute an external program: `:!{command}`
* Execute an external program, redirecting output to this buffer: `:r!{command}`

Presenter Notes
----
* This is where vim shines. It allows you to interact with other processes
  simply and easily, giving you ultimate flexibility.

----

Some Personal Favorite Pipes
====

Presenter Notes
----
* Things we didn't talk about, but which you'll see:
  * windows, tabs and buffers
  * folding

----

`sort`
----

* `:{range}!sort`

Presenter Notes
----
* Demonstrate sorting an assoc array

----

`ls`
----

* `:!ls`

Presenter Notes
----
* Demonstrate listing a directory, as well as capturing it into the file
* We'll see another tool for this later

----

`tree`
----

* `:!tree {directory}`

Presenter Notes
----
* Demonstrate

----

Execute with PHP
----

* `:!php %`

Or, add this to your vimrc:

* `:autocmd FileType php noremap <C-M> :w!<CR>:!$HOME/bin/php %<CR>`

Presenter Notes
----
* Talk about `:make`

----

Lint your PHP source
----

* `:!php -l %`

Or, add this to your vimrc:

* `:autocmd FileType php noremap <C-L> :w!<CR>:!$HOME/bin/php -l %<CR>`

Presenter Notes
----
* Mention you do this for JavaScript as well, using jslint

----

Execute PHPUnit
----

* `:!phpunit %`

Or, add this to your vimrc:

* `:autocmd FileType php noremap <Leader>u :w!<CR>:!$HOME/bin/phpunit %<CR>`

Presenter Notes
----
* Mention what `<Leader>` is
* Mention you need to be in the tests directory

----

PHP Manual
----

* Get pman: `pear install doc.php.net/pman`
* Use as `keywordprg` in PHP files; add this to your vimrc: `:autocmd FileType
  php set keywordprg=/path/to/bin/pman`
* Type `Ctrl-k` on a PHP function to get its man page!

Presenter Notes
----
* Demonstrate looking up a keyword

----

Plugins
====

Presenter Notes
----
* There are thousands of plugins, from syntax to themes to plugins altering
  behavior

----

Favorites: syntax highlighting
----

* Most languages/formats already are supported
* Add `:syntax on` to your vimrc
* And filetype detection: `:filetype plugin on`
* And auto indentation: `:filetype plugin indent on`
* And support for any custom filetypes you install: `:runtime! $HOME/.vim/ftdetect/*.vim`

Presenter Notes
----
* Demonstrate

----

Favorites: surround
----

* https://github.com/tpope/vim-surround
* Highlight some text, and press `s{character}`
* Favorites: quotes, parens, braces, backticks...

Presenter Notes
----
* Demonstrate

----

Favorites: Tabularize
----

* https://github.com/godlygeek/tabular
* Align text on patterns

Presenter Notes
----
* Demonstrate aligning elements of an assoc array
* Demonstrate aligning variable assignments

----

Favorites: snipMate
----

* http://www.vim.org/scripts/script.php?script_id=2540
* Create "snippets" of text, optionally with placeholders
* Invoke the snippets to reduce typing

Presenter Notes
----
* Demonstrate "accessors" snippet

----

Favorites: taglist
----

* http://vim-taglist.sourceforge.net/
* Show an outline of code elements, and allow jumping to their definitions

Presenter Notes
----
* Demonstrate this with a class file
* Note that I use `F8` to open the tag list

----

Favorites: ctags
----

* Built-in support for tag files created with exuberant ctags
  (http://ctags.sourceforge.net/)
* Use `ctags-exuberant` to create tag files for PHP (http://bit.ly/vim-mktags)
* Tell vim about your tags (`:let tagspath = {tag path}`)
* `:tag {tagname}` to jump to a tag
* `:stag {tagname}` to open a new window with the given tag
* `Ctrl-w Ctrl-]` to open a new window with the tag under the cursor

Presenter Notes
----
* `:tag` has tab-completion
* Demonstrate with EventManager

----

Favorites: NERDTree
----

* http://www.vim.org/scripts/script.php?script_id=1658
* http://github.com/scrooloose/nerdtree
* Opens a buffer showing a tree from the current working dir
* Explore the tree, open files, create bookmarks, etc.

Presenter Notes
----
* Demonstrate
* Note that I bind `<Leader>n` to open this

----

PHP Plugins: syntax/php.vim
----

* https://github.com/tobyS/vip/blob/master/.vim/ftplugin/php.vim
  (Tobias Schlitt)
* Most up-to-date PHP syntax, usually.
* Follows PEAR standards for formatting
* Has good folding rules by default

Presenter Notes
----
* Open a PHP file

----

PHP Plugins: PDV
----

* PhpDocumentor for Vim: http://www.vim.org/scripts/script.php?script_id=1355
* Can automatically create docblocks for you, or on demand

Presenter Notes
----
* I've created a key binding for this, `Ctrl-p`
* Demonstrate

----

PHP Plugins: phpcomplete.vim
----

* http://www.vim.org/scripts/script.php?script_id=3171
  https://github.com/EvanDotPro/phpcomplete.vim
* Context-sensitive code-completion for PHP

Presenter Notes
----
* You won't get things like arguments or type-hinting usually
* But it's generally good enough
* Demonstrate with EventManager

----

Collaboration Plugins: paster.vim
----

* https://github.com/weierophinney/paster.vim
* Paste file or selection to paste service of your choice
* Returns URL of new paste, and optionally opens browser window with it

----

Collaboration Plugins: Gist
----

* http://www.vim.org/scripts/script.php?script_id=2423
* https://github.com/mattn/gist-vim
* Paste file or selection as GitHub gist; returns URL of new gist
* Can also do a ton of manipulation of gists

----

Collaboration Plugins: fugitive
----

* http://www.vim.org/scripts/script.php?script_id=2975
* https://github.com/tpope/vim-fugitive
* Work with and manipulate git repositories
* "I'm not going to lie to you; fugitive.vim may very well be the best Git
  wrapper of all time.'" *-- Tim Pope, author of fugitive*

Presenter Notes
---
* This one has to be presented. There's too much info.
* Mention vcscommand, which is for CVS and SVN, but not nearly as good
* Get the link to the fugitive series on vimcasts.org

----

Productivity Plugins: Project
----

* http://www.vim.org/scripts/script.php?script_id=69
* Create a customized "workspace" of files in a project
* Apply commands to trees in the project
* Much more

Presenter Notes
----
* Demonstrate with mwop.net

----

Productivity Plugins: vimwiki + vimtask
----

* **vimwiki:** http://code.google.com/p/vimwiki/
* **vimtask:** https://github.com/samsonw/vim-task
* **vimwiki+vimtask:**
  https://github.com/weierophinney/vimwiki/tree/feature/vim-task
* **Vimwiki:** personal, file-system-based wiki, diary/journal, etc.
* **Vimtask:** dead-simple todo lists
* **Vimiwiki+Vimtask:** nirvana

Presenter Notes
----
* Open up the wiki
* Open up the diary
* Demonstrate task lists
* Mention using this with fugitive for a versioned wiki...

----

Parting shots
====

Presenter Notes
----
* I've only scratched the surface.
* Spellchecking, Integration with make, grepping files, diff resolution, etc.

----

Resources
----

* http://www.vim.org/
* My vim settings: `git clone git://mwop.net/vimrc.git`
* Stuff mentioned today (and more): http://bitly.com/w5tKYU
* This talk: http://mwop.net/slides/2012-01-28-Vim/VimUnixToolchain.html

----

Thank You!
====

* http://joind.in/4769
* http://twitter.com/weierophinney
