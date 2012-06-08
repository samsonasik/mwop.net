Designing Beautiful Software
==========

.fx: titleslide

---

Who am I?
====

.fx: image-right

Matthew Weier O'Phinney

![Matthew Weier O'Phinney](images/mwop.png "Matthew Weier O'Phinney")

* Project Lead, Zend Framework
* PHP Developer
* http://mwop.net/
* @weierophinney

----

Four Years Ago...
====

---

![Terry Chay](images/chay-small.jpg "Terry Chay")

----

A ball of nails
----

![Ball of nails](images/spiky_ball.jpg "Ball of nails")

----


What is Beauty
====


----

What is beautiful software design?
-----

![Plato](images/plato-small.png)

Presenter Notes
----

* Talk about Plato's ideas of the Good and Beauty

----

What is beautiful software design?
-----

![Not Plato](images/plato-no.png "We're not talking about Plato today")

Presenter Notes
----

* We're not going to talk philosophy today, really

----

What is beautiful software design?
-----

![Software](images/SoftwareImage.jpg "Software")

Presenter Notes
----

* We're talking about software, and how to make it beautiful

----

Software Evolution
-----

![How to go from code monkey to architect](images/evolution1.png "or, how to go from code monkey to architect")

----

Examining a real-world problem
====

Presenter Notes
----

* The Socratic method uses questions, and keeps questioning until truths are
  ferreted out. That's what we're going to do here.

----

Requirements
----

.fx: image-right

![Sending Email](images/Sending-Emails.jpg "Sending Email")

* The application needs to send email
* I know the sender is always the same
* I want to BCC an address for verification

----

.fx: smaller-code

    !php
    function shop_mail($to, $subject, $body)
    {
        $headers = "From: shop@example.com\r\n"
                .= "Bcc: shop-sent@example.com\r\n";
        mail($to, $subject, $body, $headers);
    }

----

Is it beautiful?
----

* It's succinct

----

Is it beautiful?
----

* It's succinct
* It prevents us having to specify `$headers` manually each call

----

Is it beautiful?
----

* It's succinct
* It prevents us having to specify `$headers` manually each call
* It's a little more than a wrapper on `mail()`

----

What if we introduce requirements?
----

* For instance, we add another shop on a different domain, using much (if not
  all) the same code.

----

What if we introduce requirements?
----

* For instance, we add another shop on a different domain, using much (if not
  all) the same code.
* Now the "From" and "Bcc" addresses need to be different.

----

New problems
----

* We don't want to change all the places in our code that call this function...

----

New problems
----

* We don't want to change all the places in our code that call this function...
* ... at least, not after this change.

----

New problems
----

* We don't want to change all the places in our code that call this function...
* ... at least, not after this change.
* So, let's introduce a "configuration" parameter.

----

.fx: smaller-code

    !php
    <?php
    function shop_mail($to, $subject, $body, $shop = 'original') {
        switch ($shop) {
            case: 'subdomain':
                $from = 'shop@subdomain.example.com';
                $bcc  = 'shop-sent@subdomain.example.com';
                break;
            case: 'original':
            default:
                $from = 'shop@example.com';
                $bcc  = 'shop-sent@example.com';
                break;
        }

        $headers = "From: $from\r\n"
                .= "Bcc: $bcc\r\n";
        mail($to, $subject, $body, $headers);
    }

----

Is it beautiful?
----

* Switch statements will grow, and need to be documented.

----

Is it beautiful?
----

* Switch statements will grow, and need to be documented.
* We need to know what the value of that last argument will be.

----

Is it beautiful?
----

* Switch statements will grow, and need to be documented.
* We need to know what the value of that last argument will be.
* The number of arguments may not justify wrapping `mail()`

----

Evolution: use classes
====

----

Base class
----

.fx: smaller-code

    !php
    <?php
    class ShopMail
    {
        protected static $from = 'shop@example.com';
        protected static $bcc  = 'shop-sent@example.com';
        
        public static function send($to, $subject, $body) 
        {
            $headers = "From: " . static::$from . "\r\n"
                    .= "Bcc: " . static::$bcc . "\r\n";
            mail($to, $subject, $body, $headers);
        }
    }

Presenter Notes
----

* Statically done, allowing for Late Static Binding

----

Extending class
----

.fx: smaller-code

    !php
    <?php
    class SubdomainMail extends ShopMail
    {
        protected static $from = 'shop@subdomain.example.com';
        protected static $bcc  = 'shop-sent@subdomain.example.com';
    }

Presenter Notes
----

* We extend, and modify the static members

----

Usage
----

.fx: smaller-code

    !php
    <?php
    define('MYENV', 'Subdomain');

    $mailer = MYENV . 'Mail::send';
    call_user_func($mailer, $to, $subject, $body);

Presenter Notes
----

* Dirt-simple. In fact, in PHP 5.4, we can simply call `$mailer($to, $subect,
  $body)`

----

Is it beautiful?
----

* Requires extension

----

Is it beautiful?
----

* Requires extension
* Strategy selection requires knowledge of environment

----

Is it beautiful?
----

* Requires extension
* Strategy selection requires knowledge of environment
* Debugging requires knowledge of environment

----

Evolution: use configuration
====

Presenter Notes
----

* Configuration makes it possible to program once, while still altering behavior

----

.fx: smaller-code

    !php
    <?php
    $config = new ArrayObject(array(), ArrayObject::ARRAY_AS_PROPS);
    $config->env = "Subdomain';

    $mailer = $config->env . 'Mail::send';
    call_user_func($mailer, $to, $subject, $body);

----

Is it beautiful?
----

* Would be easier to just indicate the class to use, and have it be the same
  throughout the application(s).

----

Is it beautiful?
----

* Would be easier to just indicate the class to use, and have it be the same
  throughout the application(s).
* What if I have a new requirement, such as sending HTML mails?

----

Evolution: use objects
====

Presenter Notes
----

* Up until now, we've been looking at _class-oriented_ programming
* Now we'll look at _object-oriented_ programming. 
* There's a difference

----

.fx: smaller-code

    !php
    <?php
    class Mailer
    {
        protected $from = 'shop@example.com';
        protected $bcc  = 'shop-sent@example.com';
        protected $contentType = 'text/plain';
        
        public function setFrom($from) { ... }
        public function setBcc($bcc) { ... }
        public function setContentType($type) { ... }
        public function send($to, $subject, $body)
        {
            $headers = "From: " . $this->from . "\r\n"
                    .= "Bcc: " . $this->bcc . "\r\n"
                    .= "Content-Type: " . $this->contentType . "\r\n";
            mail($to, $subject, $body, $headers);
        }
    }

----

.fx: smaller-code

    !php
    <?php
    $mailer = new Mailer();
    $mailer->setFrom($config->from)
           ->setBcc($config->bcc)
           ->setContentType('text/html');
    $mailer->send($to, $subject, $body);

----

Is it beautiful?
----

* Better, but not great

----

Is it beautiful?
----

* Better, but not great
* Specific headers are hard-coded

----

Is it beautiful?
----

* Better, but not great
* Specific headers are hard-coded
* What if we don't want to use `mail()`?

Presenter Notes
----

* For instance, we may want to use SMTP
* Or, due to volume, push to a queue

----

Take a shower
====

Presenter Notes
----

* When you need to think, or design, the best thing you can do is step away from
  your computer.
* One agile practice is to take a shower. Idea is to do something _not_
  programming, in order to spur the creative thinking processes.

----

We've identified several needs
----

* Configurable, arbitrary headers
* Configurable, arbitrary tranports

----

Evolution: use composition and interfaces
====

Presenter Notes
----

* Hollywood principle
* Interfaces allow substitution

----

.fx: smaller-code

    !php
    <?php
    interface MailTransport
    {
        public function send($to, $subject, $body, $headers);
    }

    class MailHeaders extends ArrayObject
    {
        public function toString()
        {
            $headers = '';
            foreach ($this as $header => $value) {
                $headers .= $header . ': ' . $value . "\r\n";
            }
            return $headers;
        }
    }

----

.fx: smaller-code

    !php
    <?php
    class Mailer
    {
        protected $headers, $transport;
        
        public function setHeaders(MailHeaders $headers) {
            $this->headers = $headers;
            return $this;
        }
        
        public function getHeaders() {
            return $this->headers;
        }
        
        public function setTransport(MailTransport $transport) {
            $this->transport = $transport;
            return $this;
        }

        /* ... */
    }

----

.fx: smaller-code

    !php
    <?php
    class Mailer
    {
        protected $headers, $transport;
        
        public function \__construct(MailTransport $transport)
        {
            $this->setTransport($transport);
            $this->setHeaders(new MailHeaders());
        }
        public function send($to, $subject, $body)
        {
            $this->transport->send(
                $to, $subject, $body, 
                $this->headers->toString()
            );
        }
    }

----

.fx: smaller-code

    !php
    <?php
    $mailer  = new Mailer(new SmtpTransport);
    $headers = new MailHeaders();
    // or $headers = $mailer->getHeaders();

    $headers['From']         = $config->from;
    $headers['Bcc']          = $config->bcc;
    $headers['Content-Type'] = 'text/html';
    $mailer->setHeaders($headers) // if instantiated separately
           ->send($to, $subject, $body);

----

Is it beautiful?
----

* Headers management and serialization is self-contained

----

Is it beautiful?
----

* Headers management and serialization is self-contained
* Sending is separate from message composition 

----

Problems
----

* No validation or normalization of header keys
* No validation of header values
* Should a message send itself? or should we pass a message to the transport?

----

Evolution: object relations
====

----

* Objects should do one thing, and one thing well
* Use objects as messages between objects

----

Message
----

.fx: smaller-code

    !php
    <?php
    interface MailMessage
    {
        public function setTo($to);
        public function setSubject($subject);
        public function setBody($body);
        public function setHeaders(MailHeaders $headers);
        
        public function getTo();
        public function getSubject();
        public function getBody();
        public function getHeaders();
    }

Presenter Notes
----

* The mail message is now simply a value object
* Note that it _composes_ headers

----

Headers and Transport
----

.fx: smaller-code

    !php
    <?php
    interface MailHeaders
    {
        public function addHeader($header, $value);
        public function toString();
    }

    interface MailTransport
    {
        public function send(MailMessage $message);
    }

Presenter Notes
----

* Instead of an ArrayObject, we have an object with a dedicated method for
  adding headers, allowing us to validate them.
* toString() allows us to serialize the headers into a format that the transport
  can use
* The mail transport sends a _message_

----

Usage
----

.fx: smaller-code

    !php
    <?php
    $message = new Message();
    $headers = new MessageHeaders();
    $headers->addHeader('From', $from)
            ->addHeader('Content-Type', 'text/html');
    $message->setTo($to)
            ->setSubject($subject)
            ->setBody($body)
            ->setHeaders($headers);
    $transport = new SmtpTransport($config->transport);
    $transport->send($message);

----

Is it beautiful?
----

* Headers can now potentially have validation and normalization...

----

Is it beautiful?
----

* Headers can now potentially have validation and normalization...
    * ... and implementation can be varied if necessary.

----

Is it beautiful?
----

* Headers can now potentially have validation and normalization...
    * ... and implementation can be varied if necessary.
* The message is self-contained, and contains all metadata related to it.

----

Is it beautiful?
----

* Headers can now potentially have validation and normalization...
    * ... and implementation can be varied if necessary.
* The message is self-contained, and contains all metadata related to it.
* The mail transport accepts a message, and determines what to use from it, and
  how.

----

What are we really asking?
====

Presenter Notes
----

* I've used the phrase "Is it beautiful?" throughout as a meme.
* It's now time to figure out what we're really asking.

----

Is it beautiful...
----

* Can I maintain it?

Presenter Notes
----

* The new solution allows for substitution. 
* Which means we can mock things.
* Which means we can test things.

----

Is it beautiful...
----

* Can I maintain it?
* Can I change behavior when needed?

Presenter Notes
----

* The new solution allows for substitution.
* Which allows me to provide an alternate implementation of the interface.
* Which means I can change behavior.

----

Other ideas to investigate
----

* SOLID principles

Presenter Notes
----

* eek!

----

Other ideas to investigate
----

* Aspect Oriented Programming and Event-Driven Programming

Presenter Notes
----

* Allows providing inflection points within code
* Introduce cross-cutting concerns
* or allow replacing or short-circuiting implementation

----

Other ideas to investigate
----

* Testing, testing, testing!
    * Define requirements as tests
    * Exercise your API before you write it
    * Gain confidence in your code

Presenter Notes
----

* If you can't test it, you can't maintain it, and you likely can't alter
  behavior easily.
* Investigate continuous integration and deployment

----

Other ideas to investigate
----

* Adopt a coding standard

Presenter Notes
----

* Seems basic, but it's critical to making code easy to read, both for you and
  your team

----

Trade offs
----

You can always write shorter code. The trade off is re-use.

* The original code sample was fine. But we couldn't:
    * configure it
    * change its behavior

----

Trade offs
----

You can always write shorter code. The trade off is re-use.

* The original code sample was fine. But we couldn't:
    * configure it
    * change its behavior
* You end up writing the same code over and over.

----

Trade offs
----

Writing robust code usually requires _some_ verbosity.

* Separating concerns leads to a proliferation of objects and files.

----

Trade offs
----

Writing robust code usually requires _some_ verbosity.

* Separating concerns leads to a proliferation of objects and files.
* *Get over it.*

----

Trade offs
----

Writing robust code usually requires _some_ verbosity.

* Separating concerns leads to a proliferation of objects and files.
* *Get over it.*
* It's easier to digest small bites than it is an entire roast.

----

Trade offs
----

Configuration can be either inline, or from a container.

* Inline is nice, but difficult to swap out.

----

Trade offs
----

Configuration can be either inline, or from a container.

* Inline is nice, but difficult to swap out.
* Containers are nice, but now you need to pass the container around.

----

Closing thoughts
----

* Balls of nails get the job done.
* So do precision instruments.


----

Closing thoughts
----

.fx: image-right

![Van Gogh Sunflowers](images/van-gogh-sunflowers.png "Van Gogn Sunflowers")

* Think about long-term maintainability
* Consider re-usability

----

Thank You!
----

* Feedback:
    * https://joind.in/6231
    * https://twitter.com/weierophinney
    * https://github.com/weierophinney
