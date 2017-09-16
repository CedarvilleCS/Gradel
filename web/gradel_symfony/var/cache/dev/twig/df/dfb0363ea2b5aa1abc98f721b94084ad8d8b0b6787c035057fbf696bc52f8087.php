<?php

/* @Framework/Form/email_widget.html.php */
class __TwigTemplate_713984917fdd4d9a349163b903ec4ce0a2f56963f87d974c830135b6be70d571 extends Twig_Template
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
        $__internal_cd7aef0c2c3f488431db992b345d1bdd457b7335340d3cf3cf6e161a8263f757 = $this->env->getExtension("Symfony\\Bundle\\WebProfilerBundle\\Twig\\WebProfilerExtension");
        $__internal_cd7aef0c2c3f488431db992b345d1bdd457b7335340d3cf3cf6e161a8263f757->enter($__internal_cd7aef0c2c3f488431db992b345d1bdd457b7335340d3cf3cf6e161a8263f757_prof = new Twig_Profiler_Profile($this->getTemplateName(), "template", "@Framework/Form/email_widget.html.php"));

        $__internal_3ffbad3e6471f7b3a4be96cf2855ba230ad059511b080c30c3e5f67a3dccce7c = $this->env->getExtension("Symfony\\Bridge\\Twig\\Extension\\ProfilerExtension");
        $__internal_3ffbad3e6471f7b3a4be96cf2855ba230ad059511b080c30c3e5f67a3dccce7c->enter($__internal_3ffbad3e6471f7b3a4be96cf2855ba230ad059511b080c30c3e5f67a3dccce7c_prof = new Twig_Profiler_Profile($this->getTemplateName(), "template", "@Framework/Form/email_widget.html.php"));

        // line 1
        echo "<?php echo \$view['form']->block(\$form, 'form_widget_simple', array('type' => isset(\$type) ? \$type : 'email')) ?>
";
        
        $__internal_cd7aef0c2c3f488431db992b345d1bdd457b7335340d3cf3cf6e161a8263f757->leave($__internal_cd7aef0c2c3f488431db992b345d1bdd457b7335340d3cf3cf6e161a8263f757_prof);

        
        $__internal_3ffbad3e6471f7b3a4be96cf2855ba230ad059511b080c30c3e5f67a3dccce7c->leave($__internal_3ffbad3e6471f7b3a4be96cf2855ba230ad059511b080c30c3e5f67a3dccce7c_prof);

    }

    public function getTemplateName()
    {
        return "@Framework/Form/email_widget.html.php";
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
        return new Twig_Source("<?php echo \$view['form']->block(\$form, 'form_widget_simple', array('type' => isset(\$type) ? \$type : 'email')) ?>
", "@Framework/Form/email_widget.html.php", "/var/www/gradel_dev/tgsmith/test/vendor/symfony/symfony/src/Symfony/Bundle/FrameworkBundle/Resources/views/Form/email_widget.html.php");
    }
}
