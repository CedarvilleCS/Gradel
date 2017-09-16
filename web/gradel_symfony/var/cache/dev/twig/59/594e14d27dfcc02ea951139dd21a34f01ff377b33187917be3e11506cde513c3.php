<?php

/* @Framework/Form/number_widget.html.php */
class __TwigTemplate_bf45f72da4d6ed0d615296c5ca2f9d807ac8bf6041ff3d6e9a17b416c5d60617 extends Twig_Template
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
        $__internal_2304a6d4850132a7882f1868b145e0eadbbe45e88a9373d5caebb2003f836600 = $this->env->getExtension("Symfony\\Bundle\\WebProfilerBundle\\Twig\\WebProfilerExtension");
        $__internal_2304a6d4850132a7882f1868b145e0eadbbe45e88a9373d5caebb2003f836600->enter($__internal_2304a6d4850132a7882f1868b145e0eadbbe45e88a9373d5caebb2003f836600_prof = new Twig_Profiler_Profile($this->getTemplateName(), "template", "@Framework/Form/number_widget.html.php"));

        $__internal_beb649dfdd6f121b7aebf498c76fee43537eb38a985b32dcd9d252c9936c6026 = $this->env->getExtension("Symfony\\Bridge\\Twig\\Extension\\ProfilerExtension");
        $__internal_beb649dfdd6f121b7aebf498c76fee43537eb38a985b32dcd9d252c9936c6026->enter($__internal_beb649dfdd6f121b7aebf498c76fee43537eb38a985b32dcd9d252c9936c6026_prof = new Twig_Profiler_Profile($this->getTemplateName(), "template", "@Framework/Form/number_widget.html.php"));

        // line 1
        echo "<?php echo \$view['form']->block(\$form, 'form_widget_simple', array('type' => isset(\$type) ? \$type : 'text')) ?>
";
        
        $__internal_2304a6d4850132a7882f1868b145e0eadbbe45e88a9373d5caebb2003f836600->leave($__internal_2304a6d4850132a7882f1868b145e0eadbbe45e88a9373d5caebb2003f836600_prof);

        
        $__internal_beb649dfdd6f121b7aebf498c76fee43537eb38a985b32dcd9d252c9936c6026->leave($__internal_beb649dfdd6f121b7aebf498c76fee43537eb38a985b32dcd9d252c9936c6026_prof);

    }

    public function getTemplateName()
    {
        return "@Framework/Form/number_widget.html.php";
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
        return new Twig_Source("<?php echo \$view['form']->block(\$form, 'form_widget_simple', array('type' => isset(\$type) ? \$type : 'text')) ?>
", "@Framework/Form/number_widget.html.php", "/var/www/gradel_dev/tgsmith/test/vendor/symfony/symfony/src/Symfony/Bundle/FrameworkBundle/Resources/views/Form/number_widget.html.php");
    }
}
