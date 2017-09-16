<?php

/* @Framework/Form/widget_container_attributes.html.php */
class __TwigTemplate_dbcefa1d206799ff88b4b5226a9d30f3335b8606928c730cf6f3943107d3cf3c extends Twig_Template
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
        $__internal_e0641040f78ab076542bae3dff56c0327386d4a76cf4a50e4428803025c2a30c = $this->env->getExtension("Symfony\\Bundle\\WebProfilerBundle\\Twig\\WebProfilerExtension");
        $__internal_e0641040f78ab076542bae3dff56c0327386d4a76cf4a50e4428803025c2a30c->enter($__internal_e0641040f78ab076542bae3dff56c0327386d4a76cf4a50e4428803025c2a30c_prof = new Twig_Profiler_Profile($this->getTemplateName(), "template", "@Framework/Form/widget_container_attributes.html.php"));

        $__internal_d3e340faf2c54991ab5be642c30b32a63eb02043f4922ded30a254408d03b6a3 = $this->env->getExtension("Symfony\\Bridge\\Twig\\Extension\\ProfilerExtension");
        $__internal_d3e340faf2c54991ab5be642c30b32a63eb02043f4922ded30a254408d03b6a3->enter($__internal_d3e340faf2c54991ab5be642c30b32a63eb02043f4922ded30a254408d03b6a3_prof = new Twig_Profiler_Profile($this->getTemplateName(), "template", "@Framework/Form/widget_container_attributes.html.php"));

        // line 1
        echo "<?php if (!empty(\$id)): ?>id=\"<?php echo \$view->escape(\$id) ?>\"<?php endif ?>
<?php echo \$attr ? ' '.\$view['form']->block(\$form, 'attributes') : '' ?>
";
        
        $__internal_e0641040f78ab076542bae3dff56c0327386d4a76cf4a50e4428803025c2a30c->leave($__internal_e0641040f78ab076542bae3dff56c0327386d4a76cf4a50e4428803025c2a30c_prof);

        
        $__internal_d3e340faf2c54991ab5be642c30b32a63eb02043f4922ded30a254408d03b6a3->leave($__internal_d3e340faf2c54991ab5be642c30b32a63eb02043f4922ded30a254408d03b6a3_prof);

    }

    public function getTemplateName()
    {
        return "@Framework/Form/widget_container_attributes.html.php";
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
        return new Twig_Source("<?php if (!empty(\$id)): ?>id=\"<?php echo \$view->escape(\$id) ?>\"<?php endif ?>
<?php echo \$attr ? ' '.\$view['form']->block(\$form, 'attributes') : '' ?>
", "@Framework/Form/widget_container_attributes.html.php", "/var/www/gradel_dev/tgsmith/test/vendor/symfony/symfony/src/Symfony/Bundle/FrameworkBundle/Resources/views/Form/widget_container_attributes.html.php");
    }
}
