<?php

/* @Framework/Form/collection_widget.html.php */
class __TwigTemplate_dc07abe9fd8a700564163bf6b544aa621a1cadfb29de2f7b85bbe5da47b87401 extends Twig_Template
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
        $__internal_77697b3b43746f98415fe4bbb9abac8cd15da47ec35b1a3f69f8c43db1cc63bf = $this->env->getExtension("Symfony\\Bundle\\WebProfilerBundle\\Twig\\WebProfilerExtension");
        $__internal_77697b3b43746f98415fe4bbb9abac8cd15da47ec35b1a3f69f8c43db1cc63bf->enter($__internal_77697b3b43746f98415fe4bbb9abac8cd15da47ec35b1a3f69f8c43db1cc63bf_prof = new Twig_Profiler_Profile($this->getTemplateName(), "template", "@Framework/Form/collection_widget.html.php"));

        $__internal_8166db8618278b4a1fa024053f082bf8084521b004af9ce8b25c57d83f1d14a0 = $this->env->getExtension("Symfony\\Bridge\\Twig\\Extension\\ProfilerExtension");
        $__internal_8166db8618278b4a1fa024053f082bf8084521b004af9ce8b25c57d83f1d14a0->enter($__internal_8166db8618278b4a1fa024053f082bf8084521b004af9ce8b25c57d83f1d14a0_prof = new Twig_Profiler_Profile($this->getTemplateName(), "template", "@Framework/Form/collection_widget.html.php"));

        // line 1
        echo "<?php if (isset(\$prototype)): ?>
    <?php \$attr['data-prototype'] = \$view->escape(\$view['form']->row(\$prototype)) ?>
<?php endif ?>
<?php echo \$view['form']->widget(\$form, array('attr' => \$attr)) ?>
";
        
        $__internal_77697b3b43746f98415fe4bbb9abac8cd15da47ec35b1a3f69f8c43db1cc63bf->leave($__internal_77697b3b43746f98415fe4bbb9abac8cd15da47ec35b1a3f69f8c43db1cc63bf_prof);

        
        $__internal_8166db8618278b4a1fa024053f082bf8084521b004af9ce8b25c57d83f1d14a0->leave($__internal_8166db8618278b4a1fa024053f082bf8084521b004af9ce8b25c57d83f1d14a0_prof);

    }

    public function getTemplateName()
    {
        return "@Framework/Form/collection_widget.html.php";
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
        return new Twig_Source("<?php if (isset(\$prototype)): ?>
    <?php \$attr['data-prototype'] = \$view->escape(\$view['form']->row(\$prototype)) ?>
<?php endif ?>
<?php echo \$view['form']->widget(\$form, array('attr' => \$attr)) ?>
", "@Framework/Form/collection_widget.html.php", "/var/www/gradel_dev/tgsmith/test/vendor/symfony/symfony/src/Symfony/Bundle/FrameworkBundle/Resources/views/Form/collection_widget.html.php");
    }
}
