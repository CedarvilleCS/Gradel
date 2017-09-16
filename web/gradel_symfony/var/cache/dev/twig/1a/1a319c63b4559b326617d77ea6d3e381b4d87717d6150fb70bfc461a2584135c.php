<?php

/* @Framework/Form/textarea_widget.html.php */
class __TwigTemplate_bb2b793c714a4c7b7ad196a7f3feac4ea4035e52c86d4616c88890c8fac5d40a extends Twig_Template
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
        $__internal_886312d5a1a18badaea0feb1b5f0eafce8d7011c0e4b395b87e25f616114d48e = $this->env->getExtension("Symfony\\Bundle\\WebProfilerBundle\\Twig\\WebProfilerExtension");
        $__internal_886312d5a1a18badaea0feb1b5f0eafce8d7011c0e4b395b87e25f616114d48e->enter($__internal_886312d5a1a18badaea0feb1b5f0eafce8d7011c0e4b395b87e25f616114d48e_prof = new Twig_Profiler_Profile($this->getTemplateName(), "template", "@Framework/Form/textarea_widget.html.php"));

        $__internal_bb67d2cefa707cb9d3082ddbe4bcc7110a3abb8a123d88998867fb0142651aa8 = $this->env->getExtension("Symfony\\Bridge\\Twig\\Extension\\ProfilerExtension");
        $__internal_bb67d2cefa707cb9d3082ddbe4bcc7110a3abb8a123d88998867fb0142651aa8->enter($__internal_bb67d2cefa707cb9d3082ddbe4bcc7110a3abb8a123d88998867fb0142651aa8_prof = new Twig_Profiler_Profile($this->getTemplateName(), "template", "@Framework/Form/textarea_widget.html.php"));

        // line 1
        echo "<textarea <?php echo \$view['form']->block(\$form, 'widget_attributes') ?>><?php echo \$view->escape(\$value) ?></textarea>
";
        
        $__internal_886312d5a1a18badaea0feb1b5f0eafce8d7011c0e4b395b87e25f616114d48e->leave($__internal_886312d5a1a18badaea0feb1b5f0eafce8d7011c0e4b395b87e25f616114d48e_prof);

        
        $__internal_bb67d2cefa707cb9d3082ddbe4bcc7110a3abb8a123d88998867fb0142651aa8->leave($__internal_bb67d2cefa707cb9d3082ddbe4bcc7110a3abb8a123d88998867fb0142651aa8_prof);

    }

    public function getTemplateName()
    {
        return "@Framework/Form/textarea_widget.html.php";
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
        return new Twig_Source("<textarea <?php echo \$view['form']->block(\$form, 'widget_attributes') ?>><?php echo \$view->escape(\$value) ?></textarea>
", "@Framework/Form/textarea_widget.html.php", "/var/www/gradel_dev/tgsmith/test/vendor/symfony/symfony/src/Symfony/Bundle/FrameworkBundle/Resources/views/Form/textarea_widget.html.php");
    }
}
