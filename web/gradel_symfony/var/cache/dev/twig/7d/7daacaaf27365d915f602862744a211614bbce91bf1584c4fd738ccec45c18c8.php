<?php

/* @Framework/Form/percent_widget.html.php */
class __TwigTemplate_cd39220119c2a26f370acbb990753943e3f85ea7c75b14eb6ff9565eb8c9726d extends Twig_Template
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
        $__internal_572083998b22489b402cc54c03c615eb3d0980fa2576b0cb3bdd9a82522d76c8 = $this->env->getExtension("Symfony\\Bundle\\WebProfilerBundle\\Twig\\WebProfilerExtension");
        $__internal_572083998b22489b402cc54c03c615eb3d0980fa2576b0cb3bdd9a82522d76c8->enter($__internal_572083998b22489b402cc54c03c615eb3d0980fa2576b0cb3bdd9a82522d76c8_prof = new Twig_Profiler_Profile($this->getTemplateName(), "template", "@Framework/Form/percent_widget.html.php"));

        $__internal_39d992d4010d06f95393e68b8390fbb9533bfb96b27e67a4e2cbb01b68226937 = $this->env->getExtension("Symfony\\Bridge\\Twig\\Extension\\ProfilerExtension");
        $__internal_39d992d4010d06f95393e68b8390fbb9533bfb96b27e67a4e2cbb01b68226937->enter($__internal_39d992d4010d06f95393e68b8390fbb9533bfb96b27e67a4e2cbb01b68226937_prof = new Twig_Profiler_Profile($this->getTemplateName(), "template", "@Framework/Form/percent_widget.html.php"));

        // line 1
        echo "<?php echo \$view['form']->block(\$form, 'form_widget_simple', array('type' => isset(\$type) ? \$type : 'text')) ?> %
";
        
        $__internal_572083998b22489b402cc54c03c615eb3d0980fa2576b0cb3bdd9a82522d76c8->leave($__internal_572083998b22489b402cc54c03c615eb3d0980fa2576b0cb3bdd9a82522d76c8_prof);

        
        $__internal_39d992d4010d06f95393e68b8390fbb9533bfb96b27e67a4e2cbb01b68226937->leave($__internal_39d992d4010d06f95393e68b8390fbb9533bfb96b27e67a4e2cbb01b68226937_prof);

    }

    public function getTemplateName()
    {
        return "@Framework/Form/percent_widget.html.php";
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
        return new Twig_Source("<?php echo \$view['form']->block(\$form, 'form_widget_simple', array('type' => isset(\$type) ? \$type : 'text')) ?> %
", "@Framework/Form/percent_widget.html.php", "/var/www/gradel_dev/tgsmith/test/vendor/symfony/symfony/src/Symfony/Bundle/FrameworkBundle/Resources/views/Form/percent_widget.html.php");
    }
}
