<?php

/* @Framework/Form/form_widget_simple.html.php */
class __TwigTemplate_d4393e45083c8322ccdfdfe957cba8acef7d04d197f660b94c417c8953aadb4b extends Twig_Template
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
        $__internal_13e5df08e6448498118eb4f7e8231134958deeb52150a08dbc32dfd31e047dfa = $this->env->getExtension("Symfony\\Bundle\\WebProfilerBundle\\Twig\\WebProfilerExtension");
        $__internal_13e5df08e6448498118eb4f7e8231134958deeb52150a08dbc32dfd31e047dfa->enter($__internal_13e5df08e6448498118eb4f7e8231134958deeb52150a08dbc32dfd31e047dfa_prof = new Twig_Profiler_Profile($this->getTemplateName(), "template", "@Framework/Form/form_widget_simple.html.php"));

        $__internal_b454176cbbfa7c16a4b808d2d163c566644085e55d2d83f5244a3a787e40401e = $this->env->getExtension("Symfony\\Bridge\\Twig\\Extension\\ProfilerExtension");
        $__internal_b454176cbbfa7c16a4b808d2d163c566644085e55d2d83f5244a3a787e40401e->enter($__internal_b454176cbbfa7c16a4b808d2d163c566644085e55d2d83f5244a3a787e40401e_prof = new Twig_Profiler_Profile($this->getTemplateName(), "template", "@Framework/Form/form_widget_simple.html.php"));

        // line 1
        echo "<input type=\"<?php echo isset(\$type) ? \$view->escape(\$type) : 'text' ?>\" <?php echo \$view['form']->block(\$form, 'widget_attributes') ?><?php if (!empty(\$value) || is_numeric(\$value)): ?> value=\"<?php echo \$view->escape(\$value) ?>\"<?php endif ?> />
";
        
        $__internal_13e5df08e6448498118eb4f7e8231134958deeb52150a08dbc32dfd31e047dfa->leave($__internal_13e5df08e6448498118eb4f7e8231134958deeb52150a08dbc32dfd31e047dfa_prof);

        
        $__internal_b454176cbbfa7c16a4b808d2d163c566644085e55d2d83f5244a3a787e40401e->leave($__internal_b454176cbbfa7c16a4b808d2d163c566644085e55d2d83f5244a3a787e40401e_prof);

    }

    public function getTemplateName()
    {
        return "@Framework/Form/form_widget_simple.html.php";
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
        return new Twig_Source("<input type=\"<?php echo isset(\$type) ? \$view->escape(\$type) : 'text' ?>\" <?php echo \$view['form']->block(\$form, 'widget_attributes') ?><?php if (!empty(\$value) || is_numeric(\$value)): ?> value=\"<?php echo \$view->escape(\$value) ?>\"<?php endif ?> />
", "@Framework/Form/form_widget_simple.html.php", "/var/www/gradel_dev/tgsmith/test/vendor/symfony/symfony/src/Symfony/Bundle/FrameworkBundle/Resources/views/Form/form_widget_simple.html.php");
    }
}
