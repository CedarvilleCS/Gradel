<?php

/* @Framework/Form/form_row.html.php */
class __TwigTemplate_1a49977e5395a82ac8a7750f8dddbbf2e834be1e31711ae2a638825303e6ad01 extends Twig_Template
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
        $__internal_ae6c262e2ee296efab936d37cbfa39b1a9cd8d497fee936f4f9e5694bcd12c82 = $this->env->getExtension("Symfony\\Bundle\\WebProfilerBundle\\Twig\\WebProfilerExtension");
        $__internal_ae6c262e2ee296efab936d37cbfa39b1a9cd8d497fee936f4f9e5694bcd12c82->enter($__internal_ae6c262e2ee296efab936d37cbfa39b1a9cd8d497fee936f4f9e5694bcd12c82_prof = new Twig_Profiler_Profile($this->getTemplateName(), "template", "@Framework/Form/form_row.html.php"));

        $__internal_d865e6e71f7d3bebe01206b5576b5cf9ba19e569ffdb7d65b97f0a94b4aa5c00 = $this->env->getExtension("Symfony\\Bridge\\Twig\\Extension\\ProfilerExtension");
        $__internal_d865e6e71f7d3bebe01206b5576b5cf9ba19e569ffdb7d65b97f0a94b4aa5c00->enter($__internal_d865e6e71f7d3bebe01206b5576b5cf9ba19e569ffdb7d65b97f0a94b4aa5c00_prof = new Twig_Profiler_Profile($this->getTemplateName(), "template", "@Framework/Form/form_row.html.php"));

        // line 1
        echo "<div>
    <?php echo \$view['form']->label(\$form) ?>
    <?php echo \$view['form']->errors(\$form) ?>
    <?php echo \$view['form']->widget(\$form) ?>
</div>
";
        
        $__internal_ae6c262e2ee296efab936d37cbfa39b1a9cd8d497fee936f4f9e5694bcd12c82->leave($__internal_ae6c262e2ee296efab936d37cbfa39b1a9cd8d497fee936f4f9e5694bcd12c82_prof);

        
        $__internal_d865e6e71f7d3bebe01206b5576b5cf9ba19e569ffdb7d65b97f0a94b4aa5c00->leave($__internal_d865e6e71f7d3bebe01206b5576b5cf9ba19e569ffdb7d65b97f0a94b4aa5c00_prof);

    }

    public function getTemplateName()
    {
        return "@Framework/Form/form_row.html.php";
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
        return new Twig_Source("<div>
    <?php echo \$view['form']->label(\$form) ?>
    <?php echo \$view['form']->errors(\$form) ?>
    <?php echo \$view['form']->widget(\$form) ?>
</div>
", "@Framework/Form/form_row.html.php", "/var/www/gradel_dev/tgsmith/test/vendor/symfony/symfony/src/Symfony/Bundle/FrameworkBundle/Resources/views/Form/form_row.html.php");
    }
}
