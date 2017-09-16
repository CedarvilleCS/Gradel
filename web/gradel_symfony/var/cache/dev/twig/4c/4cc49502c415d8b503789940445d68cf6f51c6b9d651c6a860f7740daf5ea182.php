<?php

/* @Framework/FormTable/button_row.html.php */
class __TwigTemplate_1f9425598ab0fbe7741ee69a19d9d559f48c85838e4044d26f38f8d49a4f1877 extends Twig_Template
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
        $__internal_1575466d13fbbe54e9fa36a47843116f2b274dd914769c09c4ce1da258fa7fb0 = $this->env->getExtension("Symfony\\Bundle\\WebProfilerBundle\\Twig\\WebProfilerExtension");
        $__internal_1575466d13fbbe54e9fa36a47843116f2b274dd914769c09c4ce1da258fa7fb0->enter($__internal_1575466d13fbbe54e9fa36a47843116f2b274dd914769c09c4ce1da258fa7fb0_prof = new Twig_Profiler_Profile($this->getTemplateName(), "template", "@Framework/FormTable/button_row.html.php"));

        $__internal_5d1b0a857518a5e53d07d37a733719fa78320d99342d68a671bd0eea9e938e1b = $this->env->getExtension("Symfony\\Bridge\\Twig\\Extension\\ProfilerExtension");
        $__internal_5d1b0a857518a5e53d07d37a733719fa78320d99342d68a671bd0eea9e938e1b->enter($__internal_5d1b0a857518a5e53d07d37a733719fa78320d99342d68a671bd0eea9e938e1b_prof = new Twig_Profiler_Profile($this->getTemplateName(), "template", "@Framework/FormTable/button_row.html.php"));

        // line 1
        echo "<tr>
    <td></td>
    <td>
        <?php echo \$view['form']->widget(\$form) ?>
    </td>
</tr>
";
        
        $__internal_1575466d13fbbe54e9fa36a47843116f2b274dd914769c09c4ce1da258fa7fb0->leave($__internal_1575466d13fbbe54e9fa36a47843116f2b274dd914769c09c4ce1da258fa7fb0_prof);

        
        $__internal_5d1b0a857518a5e53d07d37a733719fa78320d99342d68a671bd0eea9e938e1b->leave($__internal_5d1b0a857518a5e53d07d37a733719fa78320d99342d68a671bd0eea9e938e1b_prof);

    }

    public function getTemplateName()
    {
        return "@Framework/FormTable/button_row.html.php";
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
        return new Twig_Source("<tr>
    <td></td>
    <td>
        <?php echo \$view['form']->widget(\$form) ?>
    </td>
</tr>
", "@Framework/FormTable/button_row.html.php", "/var/www/gradel_dev/tgsmith/test/vendor/symfony/symfony/src/Symfony/Bundle/FrameworkBundle/Resources/views/FormTable/button_row.html.php");
    }
}
