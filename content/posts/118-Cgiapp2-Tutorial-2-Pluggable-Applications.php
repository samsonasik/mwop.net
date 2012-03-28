<?php
use PhlyBlog\EntryEntity;

$entry = new EntryEntity();

$entry->setId('118-Cgiapp2-Tutorial-2-Pluggable-Applications');
$entry->setTitle('Cgiapp2 Tutorial 2: Pluggable Applications');
$entry->setAuthor('matthew');
$entry->setDraft(false);
$entry->setPublic(true);
$entry->setCreated(1149639240);
$entry->setUpdated(1149602138);
$entry->setTimezone('America/New_York');
$entry->setMetadata(array (
  'ep_access' => 'public',
));
$entry->setTags(array (
  0 => 'php',
));

$body =<<<'EOT'
<p>
    This is the second in a <a href="http://weierophinney.net/matthew/index.php?serendipity%5Baction%5D=search&serendipity%5BsearchTerm%5D=Cgiapp2+Tutorial">series of Cgiapp2 Tutorials</a>. 
    In this tutorial, I cover creating what I call 'Pluggable Applications',
    applications that can be distributed and customized to work with other
    sites.
</p>
EOT;
$entry->setBody($body);

$extended=<<<'EOT'
<h2>Background</h2>
<p>
    In several positions I've held over the years, I've needed to use the same
    application with very slight modifications on multiple sites. These include
    article applications, galleries, contact forms, and more. The underlying
    code typically remains the same -- but I may need to plug the content into a
    different sitewide template, or require authentication.
</p>
<p>
    When following <a href="http://en.wikipedia.org/wiki/MVC_Design_Pattern">MVC</a> 
    well, many of these tasks are already easy. You can create a new instance of
    the controller at will, and slip in new templates to customize the look and
    feel. However, things like a sitewide template fall out of the scope of the
    application templates; they are designed for a <em>suite</em> of
    applications. Authentication is also typically very site dependent; sure,
    your application may require a logged in user, but should you really dictate
    what credentials are used? shouldn't that be left up to the site owner?
</p>
<p>
    A good Controller can provide this kind of flexibility. With Cgiapp2's
    callback hook system, you actually don't need to develop the application to
    do some of these tasks; instead, you can leave it up to the end-user
    developer to implement. This provides strong portability and incredible
    flexibility in your applications.
</p>
<h2>Example 1: Add content to a sitewide template</h2>
<p>
    One of the most common tasks I need to undertake is to add the content
    generated by an application to a sitewide template. In Cgiapp, you can do
    this via <a href="http://cgiapp.sourceforge.net/cgiapp2_doc/Cgiapp2/Cgiapp2.html#methodcgiapp_postrun">cgiapp_postrun()</a>.
    cgiapp_postrun() is executed after the application logic is done, and
    receives the generated content as its first argument:
</p>
<div class="example"><pre><code lang="php">
public function cgiapp_postrun($body, $cgiapp)
{
    $cgiapp-&gt;tmpl_assign('content', $body);
    return $cgiapp-&gt;load_tmpl('site.phtml');
}
</code></pre></div>
<p>
    However, if you do this in your application logic, you're deciding things
    for later users of the application. Leave it out. Instead, let the end-user
    developer register their own postrun hook to do this. As an example:
</p>
<div class="example"><pre><code lang="php">
require_once 'Cgiapp2.class.php';
require_once 'My/Article.php';

class OurSite
{
    public static function postrun($body, Cgiapp2 $cgiapp)
    {
        $cgiapp-&gt;tmpl_assign('content', $body);
        return $cgiapp-&gt;load_tmpl('site.phtml');
    }
}
Cgiapp2::add_callback('postrun', array('OurSite', 'postrun'), 'My_Article');

$app = new My_Article($options);
$app-&gt;run();
</code></pre></div>
<p>
    The developer has now registered a 'postrun' hook with the My_Article
    application. When the application flow hits the postrun event, it will
    trigger OurSite::postrun(), which can then manipulate the content, throwing
    it into the developer's sitewide template.
</p>
<h2>Example 2: Adding authentication</h2>
<p>
    What if an article application doesn't require authentication, but you, as
    the end-user developer, want to require it for the edit, add, and delete
    views?  Register a prerun action. Using our example from above:
</p>
<div class="example"><pre><code lang="php">
require_once 'Cgiapp2.class.php';
require_once 'My/Article.php';

class OurSite
{
    protected static function _validate($user, $pass)
    {
        // ... validate user ...
    }

    public static function prerun($action, Cgiapp2 $cgiapp)
    {
        if (in_array($action, array('add', 'edit', 'delete')) {
            // Need to authenticate...
            require_once 'Phly/Auth.php';
            $auth = new Phly_Auth(array(self, '_validate'));
            $auth-&gt;start();
            if (!$auth-&gt;isValid) {
                // reset to the default entry for the app
                $cgiapp-&gt;prerun_mode($cgiapp-&gt;start_mode());
                return;
            }
        }
    }

    public static function postrun($body, Cgiapp2 $cgiapp)
    {
        $cgiapp-&gt;tmpl_assign('content', $body);
        return $cgiapp-&gt;load_tmpl('site.phtml');
    }
}
Cgiapp2::add_callback('prerun', array('OurSite', 'prerun'), 'My_Article');
Cgiapp2::add_callback('postrun', array('OurSite', 'postrun'), 'My_Article');

$app = new My_Article($options);
$app-&gt;run();
</code></pre></div>
<p>
    The example above uses <a href="http://weierophinney.net/phly/doc/Phly/Phly_Auth.html">Phly_Auth</a>, 
    but could as easily use another authentication mechanism. 
</p>
<p>
    The prerun hook is executed just prior to running the requested action, and
    receives the requested action as its first argument. In the code above, we
    check to see if the action requires authentication, and if so, check the
    user's credentials. If they are not authenticated, we then reset the action
    to the default entry action.
</p>
<h2>Summary</h2>
<p>
    Cgiapp2's callback hook system allows incredible opportunities for end-user
    developers to customize applications on a per-instance basis. This tutorial
    covered two hooks, prerun and postrun, but several more are available, and
    Cgiapp2 provides the means for creating additional hooks in your
    applications.
</p>
<p>
    For more information on this topic, see <a href="http://cgiapp.sourceforge.net/cgiapp2_doc/Cgiapp2/tutorial_Cgiapp24.cls.html">the callback hook documentation</a>.
</p>
EOT;
$entry->setExtended($extended);

return $entry;