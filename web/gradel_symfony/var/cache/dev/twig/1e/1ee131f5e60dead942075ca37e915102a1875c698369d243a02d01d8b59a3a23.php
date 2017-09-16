<?php

/* @Framework/Form/search_widget.html.php */
class __TwigTemplate_1298f331132aac13e444deb978c9b2241304cfae56fd58649ec7ea41cd441424 extends Twig_Template
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
        $__internal_d0302ab6d61a2c2cff5e8096d803fce8e646a83adf120e04ffc9b01d275cc9dc = $this->env->getExtension("Symfony\\Bundle\\WebProfilerBundle\\Twig\\WebProfilerExtension");
        $__internal_d0302ab6d61a2c2cff5e8096d803fce8e646a83adf120e04ffc9b01d275cc9dc->enter($__internal_d0302ab6d61a2c2cff5e8096d803fce8e646a83adf120e04ffc9b01d275cc9dc_prof = new Twig_Profiler_Profile($this->getTemplateName(), "template", "@Framework/Form/search_widget.html.php"));

        $__internal_ad1dade174115230d5dcd194c32dd02a061caca1aa04fcc1b0650e91d6ffaa09 = $this->env->getExtension("Symfony\\Bridge\\Twig\\Extension\\ProfilerExtension");
        $__internal_ad1dade174115230d5dcd194c32dd02a061caca1aa04fcc1b0650e91d6ffaa09->enter($__internal_ad1dade174115230d5dcd194c32dd02a061caca1aa04fcc1b0650e91d6ffaa09_prof = new Twig_Profiler_Profile($this->getTemplateName(), "template", "@Framework/Form/search_widget.html.php"));

        // line 1
        echo "<?php echo \$view['form']->block(\$form, 'form_widget_simple', array('type' => isset(\$type) ? \$type : 'search')) ?>
";
        
        $__internal_d0302ab6d61a2c2cff5e8096d803fce8e646a83adf120e04ffc9b01d275cc9dc->leave($__internal_d0302ab6d61a2c2cff5e8096d803fce8e646a83adf120e04ffc9b01d275cc9dc_prof);

        
        $__internal_ad1dade174115230d5dcd194c32dd02a061caca1aa04fcc1b0650e91d6ffaa09->leave($__internal_ad1dade174115230d5dcd194c32dd02a061caca1aa04fcc1b0650e91d6ffaa09_prof);

    }

    public function getTemplateName()
    {
        return "@Framework/Form/search_widget.html.php";
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
        return new Twig_Source("<?php echo \$view['form']->block(\$form, 'form_widget_simple', array('type' => isset(\$type) ? \$type : 'search')) ?>
", "@Framework/Form/search_widget.html.php", "/var/www/gradel_dev/tgsmith/test/vendor/symfony/symfony/src/Symfony/Bundle/FrameworkBundle/Resources/views/Form/search_widget.html.php");
    }
}
