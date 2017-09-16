<?php

/* @Framework/Form/button_attributes.html.php */
class __TwigTemplate_b2c980b809a6b329df0b4dafaf8b8e38d404b763563a567035a535fe0449600f extends Twig_Template
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
        $__internal_5d77860dcd5af8ededf13894b8b72e967e2fb353d4aafc1df9e2726d00a1905b = $this->env->getExtension("Symfony\\Bundle\\WebProfilerBundle\\Twig\\WebProfilerExtension");
        $__internal_5d77860dcd5af8ededf13894b8b72e967e2fb353d4aafc1df9e2726d00a1905b->enter($__internal_5d77860dcd5af8ededf13894b8b72e967e2fb353d4aafc1df9e2726d00a1905b_prof = new Twig_Profiler_Profile($this->getTemplateName(), "template", "@Framework/Form/button_attributes.html.php"));

        $__internal_a0e841cfe86507c809674e4493d113f76cd95e7cf8e8f716d691d5a8eb642021 = $this->env->getExtension("Symfony\\Bridge\\Twig\\Extension\\ProfilerExtension");
        $__internal_a0e841cfe86507c809674e4493d113f76cd95e7cf8e8f716d691d5a8eb642021->enter($__internal_a0e841cfe86507c809674e4493d113f76cd95e7cf8e8f716d691d5a8eb642021_prof = new Twig_Profiler_Profile($this->getTemplateName(), "template", "@Framework/Form/button_attributes.html.php"));

        // line 1
        echo "id=\"<?php echo \$view->escape(\$id) ?>\" name=\"<?php echo \$view->escape(\$full_name) ?>\"<?php if (\$disabled): ?> disabled=\"disabled\"<?php endif ?>
<?php echo \$attr ? ' '.\$view['form']->block(\$form, 'attributes') : '' ?>
";
        
        $__internal_5d77860dcd5af8ededf13894b8b72e967e2fb353d4aafc1df9e2726d00a1905b->leave($__internal_5d77860dcd5af8ededf13894b8b72e967e2fb353d4aafc1df9e2726d00a1905b_prof);

        
        $__internal_a0e841cfe86507c809674e4493d113f76cd95e7cf8e8f716d691d5a8eb642021->leave($__internal_a0e841cfe86507c809674e4493d113f76cd95e7cf8e8f716d691d5a8eb642021_prof);

    }

    public function getTemplateName()
    {
        return "@Framework/Form/button_attributes.html.php";
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
        return new Twig_Source("id=\"<?php echo \$view->escape(\$id) ?>\" name=\"<?php echo \$view->escape(\$full_name) ?>\"<?php if (\$disabled): ?> disabled=\"disabled\"<?php endif ?>
<?php echo \$attr ? ' '.\$view['form']->block(\$form, 'attributes') : '' ?>
", "@Framework/Form/button_attributes.html.php", "/var/www/gradel_dev/tgsmith/test/vendor/symfony/symfony/src/Symfony/Bundle/FrameworkBundle/Resources/views/Form/button_attributes.html.php");
    }
}
