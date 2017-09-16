<?php

/* @Framework/Form/hidden_row.html.php */
class __TwigTemplate_30e9bbb82f9fee8bc94608bd66aa6aa6ef68ed20c28369f8980de978da4aace0 extends Twig_Template
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
        $__internal_f18609eff671a8dbb914d2c73df2e95fb0dda6eb54dcbeba228903f87bd885f0 = $this->env->getExtension("Symfony\\Bundle\\WebProfilerBundle\\Twig\\WebProfilerExtension");
        $__internal_f18609eff671a8dbb914d2c73df2e95fb0dda6eb54dcbeba228903f87bd885f0->enter($__internal_f18609eff671a8dbb914d2c73df2e95fb0dda6eb54dcbeba228903f87bd885f0_prof = new Twig_Profiler_Profile($this->getTemplateName(), "template", "@Framework/Form/hidden_row.html.php"));

        $__internal_74a29ed416cf6035febdc43b7abe628fbeaad6e30054d100de5528ebc37e88a8 = $this->env->getExtension("Symfony\\Bridge\\Twig\\Extension\\ProfilerExtension");
        $__internal_74a29ed416cf6035febdc43b7abe628fbeaad6e30054d100de5528ebc37e88a8->enter($__internal_74a29ed416cf6035febdc43b7abe628fbeaad6e30054d100de5528ebc37e88a8_prof = new Twig_Profiler_Profile($this->getTemplateName(), "template", "@Framework/Form/hidden_row.html.php"));

        // line 1
        echo "<?php echo \$view['form']->widget(\$form) ?>
";
        
        $__internal_f18609eff671a8dbb914d2c73df2e95fb0dda6eb54dcbeba228903f87bd885f0->leave($__internal_f18609eff671a8dbb914d2c73df2e95fb0dda6eb54dcbeba228903f87bd885f0_prof);

        
        $__internal_74a29ed416cf6035febdc43b7abe628fbeaad6e30054d100de5528ebc37e88a8->leave($__internal_74a29ed416cf6035febdc43b7abe628fbeaad6e30054d100de5528ebc37e88a8_prof);

    }

    public function getTemplateName()
    {
        return "@Framework/Form/hidden_row.html.php";
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
        return new Twig_Source("<?php echo \$view['form']->widget(\$form) ?>
", "@Framework/Form/hidden_row.html.php", "/var/www/gradel_dev/tgsmith/test/vendor/symfony/symfony/src/Symfony/Bundle/FrameworkBundle/Resources/views/Form/hidden_row.html.php");
    }
}
