<?php

/* @Framework/Form/url_widget.html.php */
class __TwigTemplate_86b3c3e5f84ec3de242ddf571da0e436409615138873e0ffc3077f9dd18f0ed5 extends Twig_Template
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
        $__internal_6ecd3862ca8001b11102121665d146234c2196fb45bb9d8d1ce48d4ac52b1c1e = $this->env->getExtension("Symfony\\Bundle\\WebProfilerBundle\\Twig\\WebProfilerExtension");
        $__internal_6ecd3862ca8001b11102121665d146234c2196fb45bb9d8d1ce48d4ac52b1c1e->enter($__internal_6ecd3862ca8001b11102121665d146234c2196fb45bb9d8d1ce48d4ac52b1c1e_prof = new Twig_Profiler_Profile($this->getTemplateName(), "template", "@Framework/Form/url_widget.html.php"));

        $__internal_1864f355699d3f614b47f58b66e8d6b83579b92068ef1bbc333c5f25aecdd441 = $this->env->getExtension("Symfony\\Bridge\\Twig\\Extension\\ProfilerExtension");
        $__internal_1864f355699d3f614b47f58b66e8d6b83579b92068ef1bbc333c5f25aecdd441->enter($__internal_1864f355699d3f614b47f58b66e8d6b83579b92068ef1bbc333c5f25aecdd441_prof = new Twig_Profiler_Profile($this->getTemplateName(), "template", "@Framework/Form/url_widget.html.php"));

        // line 1
        echo "<?php echo \$view['form']->block(\$form, 'form_widget_simple', array('type' => isset(\$type) ? \$type : 'url')) ?>
";
        
        $__internal_6ecd3862ca8001b11102121665d146234c2196fb45bb9d8d1ce48d4ac52b1c1e->leave($__internal_6ecd3862ca8001b11102121665d146234c2196fb45bb9d8d1ce48d4ac52b1c1e_prof);

        
        $__internal_1864f355699d3f614b47f58b66e8d6b83579b92068ef1bbc333c5f25aecdd441->leave($__internal_1864f355699d3f614b47f58b66e8d6b83579b92068ef1bbc333c5f25aecdd441_prof);

    }

    public function getTemplateName()
    {
        return "@Framework/Form/url_widget.html.php";
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
        return new Twig_Source("<?php echo \$view['form']->block(\$form, 'form_widget_simple', array('type' => isset(\$type) ? \$type : 'url')) ?>
", "@Framework/Form/url_widget.html.php", "/var/www/gradel_dev/tgsmith/test/vendor/symfony/symfony/src/Symfony/Bundle/FrameworkBundle/Resources/views/Form/url_widget.html.php");
    }
}
