<?php

/* @Framework/Form/hidden_widget.html.php */
class __TwigTemplate_34b47bd1df203ea7f4138ad291afe334d91f6f4d6dcdef4eeb6e49f78f4838ea extends Twig_Template
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
        $__internal_439845e3da904ef520ca1f5fda2fcd956190fce9e9719f23fbaa8725ca2d5a06 = $this->env->getExtension("Symfony\\Bundle\\WebProfilerBundle\\Twig\\WebProfilerExtension");
        $__internal_439845e3da904ef520ca1f5fda2fcd956190fce9e9719f23fbaa8725ca2d5a06->enter($__internal_439845e3da904ef520ca1f5fda2fcd956190fce9e9719f23fbaa8725ca2d5a06_prof = new Twig_Profiler_Profile($this->getTemplateName(), "template", "@Framework/Form/hidden_widget.html.php"));

        $__internal_39c1d7898a1f011f266bc38938e3559183bbb03ee277ce9b438ea6dd4bf2e53d = $this->env->getExtension("Symfony\\Bridge\\Twig\\Extension\\ProfilerExtension");
        $__internal_39c1d7898a1f011f266bc38938e3559183bbb03ee277ce9b438ea6dd4bf2e53d->enter($__internal_39c1d7898a1f011f266bc38938e3559183bbb03ee277ce9b438ea6dd4bf2e53d_prof = new Twig_Profiler_Profile($this->getTemplateName(), "template", "@Framework/Form/hidden_widget.html.php"));

        // line 1
        echo "<?php echo \$view['form']->block(\$form, 'form_widget_simple', array('type' => isset(\$type) ? \$type : 'hidden')) ?>
";
        
        $__internal_439845e3da904ef520ca1f5fda2fcd956190fce9e9719f23fbaa8725ca2d5a06->leave($__internal_439845e3da904ef520ca1f5fda2fcd956190fce9e9719f23fbaa8725ca2d5a06_prof);

        
        $__internal_39c1d7898a1f011f266bc38938e3559183bbb03ee277ce9b438ea6dd4bf2e53d->leave($__internal_39c1d7898a1f011f266bc38938e3559183bbb03ee277ce9b438ea6dd4bf2e53d_prof);

    }

    public function getTemplateName()
    {
        return "@Framework/Form/hidden_widget.html.php";
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
        return new Twig_Source("<?php echo \$view['form']->block(\$form, 'form_widget_simple', array('type' => isset(\$type) ? \$type : 'hidden')) ?>
", "@Framework/Form/hidden_widget.html.php", "/var/www/gradel_dev/tgsmith/test/vendor/symfony/symfony/src/Symfony/Bundle/FrameworkBundle/Resources/views/Form/hidden_widget.html.php");
    }
}
