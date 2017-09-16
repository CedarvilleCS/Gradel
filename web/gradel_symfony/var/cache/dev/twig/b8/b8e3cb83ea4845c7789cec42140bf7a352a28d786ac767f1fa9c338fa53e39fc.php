<?php

/* @Framework/Form/choice_options.html.php */
class __TwigTemplate_d8a5605ebc50be684b3335547cbe613318b199fc8302d0fa558a2efce7c45f6a extends Twig_Template
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
        $__internal_26dbfbe3dc39f9f34017caadee59d0ecaec81c41af63892232d06302a790841c = $this->env->getExtension("Symfony\\Bundle\\WebProfilerBundle\\Twig\\WebProfilerExtension");
        $__internal_26dbfbe3dc39f9f34017caadee59d0ecaec81c41af63892232d06302a790841c->enter($__internal_26dbfbe3dc39f9f34017caadee59d0ecaec81c41af63892232d06302a790841c_prof = new Twig_Profiler_Profile($this->getTemplateName(), "template", "@Framework/Form/choice_options.html.php"));

        $__internal_5faa4fa77ae418f99d6e56693a52bbbdb988a714c762e9be83713afec6982b26 = $this->env->getExtension("Symfony\\Bridge\\Twig\\Extension\\ProfilerExtension");
        $__internal_5faa4fa77ae418f99d6e56693a52bbbdb988a714c762e9be83713afec6982b26->enter($__internal_5faa4fa77ae418f99d6e56693a52bbbdb988a714c762e9be83713afec6982b26_prof = new Twig_Profiler_Profile($this->getTemplateName(), "template", "@Framework/Form/choice_options.html.php"));

        // line 1
        echo "<?php echo \$view['form']->block(\$form, 'choice_widget_options') ?>
";
        
        $__internal_26dbfbe3dc39f9f34017caadee59d0ecaec81c41af63892232d06302a790841c->leave($__internal_26dbfbe3dc39f9f34017caadee59d0ecaec81c41af63892232d06302a790841c_prof);

        
        $__internal_5faa4fa77ae418f99d6e56693a52bbbdb988a714c762e9be83713afec6982b26->leave($__internal_5faa4fa77ae418f99d6e56693a52bbbdb988a714c762e9be83713afec6982b26_prof);

    }

    public function getTemplateName()
    {
        return "@Framework/Form/choice_options.html.php";
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
        return new Twig_Source("<?php echo \$view['form']->block(\$form, 'choice_widget_options') ?>
", "@Framework/Form/choice_options.html.php", "/var/www/gradel_dev/tgsmith/test/vendor/symfony/symfony/src/Symfony/Bundle/FrameworkBundle/Resources/views/Form/choice_options.html.php");
    }
}
