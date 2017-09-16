<?php

/* @Framework/Form/radio_widget.html.php */
class __TwigTemplate_31dd844eb093b73da61e315b6c11444d5183b33ab668bfa6357f586b78904800 extends Twig_Template
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
        $__internal_2591c367a14b694db19b56718a003fa4428e44122e8c8222e84e9db1c0d58dd2 = $this->env->getExtension("Symfony\\Bundle\\WebProfilerBundle\\Twig\\WebProfilerExtension");
        $__internal_2591c367a14b694db19b56718a003fa4428e44122e8c8222e84e9db1c0d58dd2->enter($__internal_2591c367a14b694db19b56718a003fa4428e44122e8c8222e84e9db1c0d58dd2_prof = new Twig_Profiler_Profile($this->getTemplateName(), "template", "@Framework/Form/radio_widget.html.php"));

        $__internal_3dede0b3f580e798c0c32804e99545ec12b2886c7cfc8519740f33c012603e38 = $this->env->getExtension("Symfony\\Bridge\\Twig\\Extension\\ProfilerExtension");
        $__internal_3dede0b3f580e798c0c32804e99545ec12b2886c7cfc8519740f33c012603e38->enter($__internal_3dede0b3f580e798c0c32804e99545ec12b2886c7cfc8519740f33c012603e38_prof = new Twig_Profiler_Profile($this->getTemplateName(), "template", "@Framework/Form/radio_widget.html.php"));

        // line 1
        echo "<input type=\"radio\"
    <?php echo \$view['form']->block(\$form, 'widget_attributes') ?>
    value=\"<?php echo \$view->escape(\$value) ?>\"
    <?php if (\$checked): ?> checked=\"checked\"<?php endif ?>
/>
";
        
        $__internal_2591c367a14b694db19b56718a003fa4428e44122e8c8222e84e9db1c0d58dd2->leave($__internal_2591c367a14b694db19b56718a003fa4428e44122e8c8222e84e9db1c0d58dd2_prof);

        
        $__internal_3dede0b3f580e798c0c32804e99545ec12b2886c7cfc8519740f33c012603e38->leave($__internal_3dede0b3f580e798c0c32804e99545ec12b2886c7cfc8519740f33c012603e38_prof);

    }

    public function getTemplateName()
    {
        return "@Framework/Form/radio_widget.html.php";
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
        return new Twig_Source("<input type=\"radio\"
    <?php echo \$view['form']->block(\$form, 'widget_attributes') ?>
    value=\"<?php echo \$view->escape(\$value) ?>\"
    <?php if (\$checked): ?> checked=\"checked\"<?php endif ?>
/>
", "@Framework/Form/radio_widget.html.php", "/var/www/gradel_dev/tgsmith/test/vendor/symfony/symfony/src/Symfony/Bundle/FrameworkBundle/Resources/views/Form/radio_widget.html.php");
    }
}
