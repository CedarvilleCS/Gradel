<?php

/* @Framework/Form/form_rest.html.php */
class __TwigTemplate_40fdfccd4f70cbe3f06da1d43dbf359498c9aefc81e7a03c5b5c26ea686db826 extends Twig_Template
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
        $__internal_bd2c76f479dfe1d71f62c0cf1b775130b358cf9951b3b2c33f49eb8412d86dd3 = $this->env->getExtension("Symfony\\Bundle\\WebProfilerBundle\\Twig\\WebProfilerExtension");
        $__internal_bd2c76f479dfe1d71f62c0cf1b775130b358cf9951b3b2c33f49eb8412d86dd3->enter($__internal_bd2c76f479dfe1d71f62c0cf1b775130b358cf9951b3b2c33f49eb8412d86dd3_prof = new Twig_Profiler_Profile($this->getTemplateName(), "template", "@Framework/Form/form_rest.html.php"));

        $__internal_3b09ade7f896e6a0f3f865c18945b38cf8d6318615fca3c6c5a8401a2f07b04a = $this->env->getExtension("Symfony\\Bridge\\Twig\\Extension\\ProfilerExtension");
        $__internal_3b09ade7f896e6a0f3f865c18945b38cf8d6318615fca3c6c5a8401a2f07b04a->enter($__internal_3b09ade7f896e6a0f3f865c18945b38cf8d6318615fca3c6c5a8401a2f07b04a_prof = new Twig_Profiler_Profile($this->getTemplateName(), "template", "@Framework/Form/form_rest.html.php"));

        // line 1
        echo "<?php foreach (\$form as \$child): ?>
    <?php if (!\$child->isRendered()): ?>
        <?php echo \$view['form']->row(\$child) ?>
    <?php endif; ?>
<?php endforeach; ?>
";
        
        $__internal_bd2c76f479dfe1d71f62c0cf1b775130b358cf9951b3b2c33f49eb8412d86dd3->leave($__internal_bd2c76f479dfe1d71f62c0cf1b775130b358cf9951b3b2c33f49eb8412d86dd3_prof);

        
        $__internal_3b09ade7f896e6a0f3f865c18945b38cf8d6318615fca3c6c5a8401a2f07b04a->leave($__internal_3b09ade7f896e6a0f3f865c18945b38cf8d6318615fca3c6c5a8401a2f07b04a_prof);

    }

    public function getTemplateName()
    {
        return "@Framework/Form/form_rest.html.php";
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
        return new Twig_Source("<?php foreach (\$form as \$child): ?>
    <?php if (!\$child->isRendered()): ?>
        <?php echo \$view['form']->row(\$child) ?>
    <?php endif; ?>
<?php endforeach; ?>
", "@Framework/Form/form_rest.html.php", "/var/www/gradel_dev/tgsmith/test/vendor/symfony/symfony/src/Symfony/Bundle/FrameworkBundle/Resources/views/Form/form_rest.html.php");
    }
}
