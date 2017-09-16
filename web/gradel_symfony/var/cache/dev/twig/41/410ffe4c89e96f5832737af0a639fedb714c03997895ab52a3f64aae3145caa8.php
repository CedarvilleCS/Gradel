<?php

/* @Framework/FormTable/hidden_row.html.php */
class __TwigTemplate_8a7ec5f1297f82813348b436c25490e6cba5ca727f589e4bd4d8646d90b99f7e extends Twig_Template
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
        $__internal_712a61e9a5ffc032ed3855ea9f50c83988dc3d8ff8970b716c257f16f0a957ca = $this->env->getExtension("Symfony\\Bundle\\WebProfilerBundle\\Twig\\WebProfilerExtension");
        $__internal_712a61e9a5ffc032ed3855ea9f50c83988dc3d8ff8970b716c257f16f0a957ca->enter($__internal_712a61e9a5ffc032ed3855ea9f50c83988dc3d8ff8970b716c257f16f0a957ca_prof = new Twig_Profiler_Profile($this->getTemplateName(), "template", "@Framework/FormTable/hidden_row.html.php"));

        $__internal_3aa13d26fe1ed433e4fe4e3dc76089a813604f1738b0e36105c0f403a0bcf92a = $this->env->getExtension("Symfony\\Bridge\\Twig\\Extension\\ProfilerExtension");
        $__internal_3aa13d26fe1ed433e4fe4e3dc76089a813604f1738b0e36105c0f403a0bcf92a->enter($__internal_3aa13d26fe1ed433e4fe4e3dc76089a813604f1738b0e36105c0f403a0bcf92a_prof = new Twig_Profiler_Profile($this->getTemplateName(), "template", "@Framework/FormTable/hidden_row.html.php"));

        // line 1
        echo "<tr style=\"display: none\">
    <td colspan=\"2\">
        <?php echo \$view['form']->widget(\$form) ?>
    </td>
</tr>
";
        
        $__internal_712a61e9a5ffc032ed3855ea9f50c83988dc3d8ff8970b716c257f16f0a957ca->leave($__internal_712a61e9a5ffc032ed3855ea9f50c83988dc3d8ff8970b716c257f16f0a957ca_prof);

        
        $__internal_3aa13d26fe1ed433e4fe4e3dc76089a813604f1738b0e36105c0f403a0bcf92a->leave($__internal_3aa13d26fe1ed433e4fe4e3dc76089a813604f1738b0e36105c0f403a0bcf92a_prof);

    }

    public function getTemplateName()
    {
        return "@Framework/FormTable/hidden_row.html.php";
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
        return new Twig_Source("<tr style=\"display: none\">
    <td colspan=\"2\">
        <?php echo \$view['form']->widget(\$form) ?>
    </td>
</tr>
", "@Framework/FormTable/hidden_row.html.php", "/var/www/gradel_dev/tgsmith/test/vendor/symfony/symfony/src/Symfony/Bundle/FrameworkBundle/Resources/views/FormTable/hidden_row.html.php");
    }
}
