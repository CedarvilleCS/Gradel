<?php

/* @Framework/Form/form.html.php */
class __TwigTemplate_6ffc495d9c4130ee704e042ed24a621a3d0265ad6f26953645620129cfb6b85e extends Twig_Template
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
        $__internal_161bc3b7ab82f3cc7e5c3b96661a6cf4cb2a101ab64f0814086e3b45f44926af = $this->env->getExtension("Symfony\\Bundle\\WebProfilerBundle\\Twig\\WebProfilerExtension");
        $__internal_161bc3b7ab82f3cc7e5c3b96661a6cf4cb2a101ab64f0814086e3b45f44926af->enter($__internal_161bc3b7ab82f3cc7e5c3b96661a6cf4cb2a101ab64f0814086e3b45f44926af_prof = new Twig_Profiler_Profile($this->getTemplateName(), "template", "@Framework/Form/form.html.php"));

        $__internal_78e2fbcd01d9848d83ad4728929f47efc7f47286f3669a67ed0beba4e6d9b8df = $this->env->getExtension("Symfony\\Bridge\\Twig\\Extension\\ProfilerExtension");
        $__internal_78e2fbcd01d9848d83ad4728929f47efc7f47286f3669a67ed0beba4e6d9b8df->enter($__internal_78e2fbcd01d9848d83ad4728929f47efc7f47286f3669a67ed0beba4e6d9b8df_prof = new Twig_Profiler_Profile($this->getTemplateName(), "template", "@Framework/Form/form.html.php"));

        // line 1
        echo "<?php echo \$view['form']->start(\$form) ?>
    <?php echo \$view['form']->widget(\$form) ?>
<?php echo \$view['form']->end(\$form) ?>
";
        
        $__internal_161bc3b7ab82f3cc7e5c3b96661a6cf4cb2a101ab64f0814086e3b45f44926af->leave($__internal_161bc3b7ab82f3cc7e5c3b96661a6cf4cb2a101ab64f0814086e3b45f44926af_prof);

        
        $__internal_78e2fbcd01d9848d83ad4728929f47efc7f47286f3669a67ed0beba4e6d9b8df->leave($__internal_78e2fbcd01d9848d83ad4728929f47efc7f47286f3669a67ed0beba4e6d9b8df_prof);

    }

    public function getTemplateName()
    {
        return "@Framework/Form/form.html.php";
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
        return new Twig_Source("<?php echo \$view['form']->start(\$form) ?>
    <?php echo \$view['form']->widget(\$form) ?>
<?php echo \$view['form']->end(\$form) ?>
", "@Framework/Form/form.html.php", "/var/www/gradel_dev/tgsmith/test/vendor/symfony/symfony/src/Symfony/Bundle/FrameworkBundle/Resources/views/Form/form.html.php");
    }
}
