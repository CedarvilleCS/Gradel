<?php

/* @Framework/Form/submit_widget.html.php */
class __TwigTemplate_020e0e5cef61a352d913c4249b53d766f5e1d00d8745c6c59df142c6398112c7 extends Twig_Template
{
    public function __construct(Twig_Environment $env)
    {
        parent::__construct($env);

        $this->parent = false;

        $this->blocks = array(
        );
    }

    protected function doDisplay(array $context, array $blocks = array())
    {
        $__internal_221bc8d86de80d6efc7425b2e8f8a6610cda0ccf61afbb5abc6eb2c2445c77f9 = $this->env->getExtension("Symfony\\Bundle\\WebProfilerBundle\\Twig\\WebProfilerExtension");
        $__internal_221bc8d86de80d6efc7425b2e8f8a6610cda0ccf61afbb5abc6eb2c2445c77f9->enter($__internal_221bc8d86de80d6efc7425b2e8f8a6610cda0ccf61afbb5abc6eb2c2445c77f9_prof = new Twig_Profiler_Profile($this->getTemplateName(), "template", "@Framework/Form/submit_widget.html.php"));

        $__internal_102007300960b3b6c12eda3e89881db945702fba546831940e211854d0d11c79 = $this->env->getExtension("Symfony\\Bridge\\Twig\\Extension\\ProfilerExtension");
        $__internal_102007300960b3b6c12eda3e89881db945702fba546831940e211854d0d11c79->enter($__internal_102007300960b3b6c12eda3e89881db945702fba546831940e211854d0d11c79_prof = new Twig_Profiler_Profile($this->getTemplateName(), "template", "@Framework/Form/submit_widget.html.php"));

        // line 1
        echo "<?php echo \$view['form']->block(\$form, 'button_widget', array('type' => isset(\$type) ? \$type : 'submit')) ?>
";
        
        $__internal_221bc8d86de80d6efc7425b2e8f8a6610cda0ccf61afbb5abc6eb2c2445c77f9->leave($__internal_221bc8d86de80d6efc7425b2e8f8a6610cda0ccf61afbb5abc6eb2c2445c77f9_prof);

        
        $__internal_102007300960b3b6c12eda3e89881db945702fba546831940e211854d0d11c79->leave($__internal_102007300960b3b6c12eda3e89881db945702fba546831940e211854d0d11c79_prof);

    }

    public function getTemplateName()
    {
        return "@Framework/Form/submit_widget.html.php";
    }

    public function getDebugInfo()
    {
        return array (  25 => 1,);
    }

    /** @deprecated since 1.27 (to be removed in 2.0). Use getSourceContext() instead */
    public function getSource()
    {
        @trigger_error('The '.__METHOD__.' method is deprecated since version 1.27 and will be removed in 2.0. Use getSourceContext() instead.', E_USER_DEPRECATED);

        return $this->getSourceContext()->getCode();
    }

    public function getSourceContext()
    {
        return new Twig_Source("<?php echo \$view['form']->block(\$form, 'button_widget', array('type' => isset(\$type) ? \$type : 'submit')) ?>
", "@Framework/Form/submit_widget.html.php", "/var/www/gradel_dev/tgsmith/test/vendor/symfony/symfony/src/Symfony/Bundle/FrameworkBundle/Resources/views/Form/submit_widget.html.php");
    }
}
