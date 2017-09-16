<?php

/* @Framework/Form/checkbox_widget.html.php */
class __TwigTemplate_9d9fbb93fdaa9652d38b93d53a010b743c354ca893d517f7804369f1ac288f5c extends Twig_Template
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
        $__internal_467c6369bfa9459c3049aca681572ec69d5cee710dfbf6866a65a3659a4dff9c = $this->env->getExtension("Symfony\\Bundle\\WebProfilerBundle\\Twig\\WebProfilerExtension");
        $__internal_467c6369bfa9459c3049aca681572ec69d5cee710dfbf6866a65a3659a4dff9c->enter($__internal_467c6369bfa9459c3049aca681572ec69d5cee710dfbf6866a65a3659a4dff9c_prof = new Twig_Profiler_Profile($this->getTemplateName(), "template", "@Framework/Form/checkbox_widget.html.php"));

        $__internal_5cdcb6badaf7378ec806717da65a1135e50ebae25a7194243524c5d8ac3d4cb4 = $this->env->getExtension("Symfony\\Bridge\\Twig\\Extension\\ProfilerExtension");
        $__internal_5cdcb6badaf7378ec806717da65a1135e50ebae25a7194243524c5d8ac3d4cb4->enter($__internal_5cdcb6badaf7378ec806717da65a1135e50ebae25a7194243524c5d8ac3d4cb4_prof = new Twig_Profiler_Profile($this->getTemplateName(), "template", "@Framework/Form/checkbox_widget.html.php"));

        // line 1
        echo "<input type=\"checkbox\"
    <?php echo \$view['form']->block(\$form, 'widget_attributes') ?>
    <?php if (strlen(\$value) > 0): ?> value=\"<?php echo \$view->escape(\$value) ?>\"<?php endif ?>
    <?php if (\$checked): ?> checked=\"checked\"<?php endif ?>
/>
";
        
        $__internal_467c6369bfa9459c3049aca681572ec69d5cee710dfbf6866a65a3659a4dff9c->leave($__internal_467c6369bfa9459c3049aca681572ec69d5cee710dfbf6866a65a3659a4dff9c_prof);

        
        $__internal_5cdcb6badaf7378ec806717da65a1135e50ebae25a7194243524c5d8ac3d4cb4->leave($__internal_5cdcb6badaf7378ec806717da65a1135e50ebae25a7194243524c5d8ac3d4cb4_prof);

    }

    public function getTemplateName()
    {
        return "@Framework/Form/checkbox_widget.html.php";
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
        return new Twig_Source("<input type=\"checkbox\"
    <?php echo \$view['form']->block(\$form, 'widget_attributes') ?>
    <?php if (strlen(\$value) > 0): ?> value=\"<?php echo \$view->escape(\$value) ?>\"<?php endif ?>
    <?php if (\$checked): ?> checked=\"checked\"<?php endif ?>
/>
", "@Framework/Form/checkbox_widget.html.php", "/var/www/gradel_dev/tgsmith/test/vendor/symfony/symfony/src/Symfony/Bundle/FrameworkBundle/Resources/views/Form/checkbox_widget.html.php");
    }
}
