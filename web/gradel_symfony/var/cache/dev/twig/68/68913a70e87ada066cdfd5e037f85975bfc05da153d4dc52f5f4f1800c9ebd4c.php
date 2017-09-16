<?php

/* @Framework/Form/integer_widget.html.php */
class __TwigTemplate_1ce16a6011d90660623ea833659dff81c3be7998ff0e02d4828cdf3b1d85117c extends Twig_Template
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
        $__internal_3c049939954911a022d4923384a536bc78a093bf25bbdb952ee37838af5b713f = $this->env->getExtension("Symfony\\Bundle\\WebProfilerBundle\\Twig\\WebProfilerExtension");
        $__internal_3c049939954911a022d4923384a536bc78a093bf25bbdb952ee37838af5b713f->enter($__internal_3c049939954911a022d4923384a536bc78a093bf25bbdb952ee37838af5b713f_prof = new Twig_Profiler_Profile($this->getTemplateName(), "template", "@Framework/Form/integer_widget.html.php"));

        $__internal_d2679e26c95785dc7739824be6ba6b7c84b0ce88cbfc4bab6ce0a982b94e0c49 = $this->env->getExtension("Symfony\\Bridge\\Twig\\Extension\\ProfilerExtension");
        $__internal_d2679e26c95785dc7739824be6ba6b7c84b0ce88cbfc4bab6ce0a982b94e0c49->enter($__internal_d2679e26c95785dc7739824be6ba6b7c84b0ce88cbfc4bab6ce0a982b94e0c49_prof = new Twig_Profiler_Profile($this->getTemplateName(), "template", "@Framework/Form/integer_widget.html.php"));

        // line 1
        echo "<?php echo \$view['form']->block(\$form, 'form_widget_simple', array('type' => isset(\$type) ? \$type : 'number')) ?>
";
        
        $__internal_3c049939954911a022d4923384a536bc78a093bf25bbdb952ee37838af5b713f->leave($__internal_3c049939954911a022d4923384a536bc78a093bf25bbdb952ee37838af5b713f_prof);

        
        $__internal_d2679e26c95785dc7739824be6ba6b7c84b0ce88cbfc4bab6ce0a982b94e0c49->leave($__internal_d2679e26c95785dc7739824be6ba6b7c84b0ce88cbfc4bab6ce0a982b94e0c49_prof);

    }

    public function getTemplateName()
    {
        return "@Framework/Form/integer_widget.html.php";
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
        return new Twig_Source("<?php echo \$view['form']->block(\$form, 'form_widget_simple', array('type' => isset(\$type) ? \$type : 'number')) ?>
", "@Framework/Form/integer_widget.html.php", "/var/www/gradel_dev/tgsmith/test/vendor/symfony/symfony/src/Symfony/Bundle/FrameworkBundle/Resources/views/Form/integer_widget.html.php");
    }
}
