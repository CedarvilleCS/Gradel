<?php

/* @Framework/Form/choice_widget_expanded.html.php */
class __TwigTemplate_744f2313507b962d752049ae4bd059d5a381e69ed189d9931b9c563e3f212da9 extends Twig_Template
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
        $__internal_a3a439d85eafc2ee3e3e27d9573ff85bf00939db7c4f995999c4ee5f7797a98b = $this->env->getExtension("Symfony\\Bundle\\WebProfilerBundle\\Twig\\WebProfilerExtension");
        $__internal_a3a439d85eafc2ee3e3e27d9573ff85bf00939db7c4f995999c4ee5f7797a98b->enter($__internal_a3a439d85eafc2ee3e3e27d9573ff85bf00939db7c4f995999c4ee5f7797a98b_prof = new Twig_Profiler_Profile($this->getTemplateName(), "template", "@Framework/Form/choice_widget_expanded.html.php"));

        $__internal_fe60601b492005f67d0162eacc370e23803953c7e2c7b36472a87c1c0c5a6f15 = $this->env->getExtension("Symfony\\Bridge\\Twig\\Extension\\ProfilerExtension");
        $__internal_fe60601b492005f67d0162eacc370e23803953c7e2c7b36472a87c1c0c5a6f15->enter($__internal_fe60601b492005f67d0162eacc370e23803953c7e2c7b36472a87c1c0c5a6f15_prof = new Twig_Profiler_Profile($this->getTemplateName(), "template", "@Framework/Form/choice_widget_expanded.html.php"));

        // line 1
        echo "<div <?php echo \$view['form']->block(\$form, 'widget_container_attributes') ?>>
<?php foreach (\$form as \$child): ?>
    <?php echo \$view['form']->widget(\$child) ?>
    <?php echo \$view['form']->label(\$child, null, array('translation_domain' => \$choice_translation_domain)) ?>
<?php endforeach ?>
</div>
";
        
        $__internal_a3a439d85eafc2ee3e3e27d9573ff85bf00939db7c4f995999c4ee5f7797a98b->leave($__internal_a3a439d85eafc2ee3e3e27d9573ff85bf00939db7c4f995999c4ee5f7797a98b_prof);

        
        $__internal_fe60601b492005f67d0162eacc370e23803953c7e2c7b36472a87c1c0c5a6f15->leave($__internal_fe60601b492005f67d0162eacc370e23803953c7e2c7b36472a87c1c0c5a6f15_prof);

    }

    public function getTemplateName()
    {
        return "@Framework/Form/choice_widget_expanded.html.php";
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
        return new Twig_Source("<div <?php echo \$view['form']->block(\$form, 'widget_container_attributes') ?>>
<?php foreach (\$form as \$child): ?>
    <?php echo \$view['form']->widget(\$child) ?>
    <?php echo \$view['form']->label(\$child, null, array('translation_domain' => \$choice_translation_domain)) ?>
<?php endforeach ?>
</div>
", "@Framework/Form/choice_widget_expanded.html.php", "/var/www/gradel_dev/tgsmith/test/vendor/symfony/symfony/src/Symfony/Bundle/FrameworkBundle/Resources/views/Form/choice_widget_expanded.html.php");
    }
}
