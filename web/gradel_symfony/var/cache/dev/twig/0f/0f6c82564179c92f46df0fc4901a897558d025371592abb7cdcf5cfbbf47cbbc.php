<?php

/* @Framework/Form/choice_attributes.html.php */
class __TwigTemplate_7fe13cb93fd8f777c42d054b74a677a34834856be41e681d3bc7cfee122abe69 extends Twig_Template
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
        $__internal_2111478d03d58d1b939e72bd100dfe531f4e6072e4c391df9e4565e598211c62 = $this->env->getExtension("Symfony\\Bundle\\WebProfilerBundle\\Twig\\WebProfilerExtension");
        $__internal_2111478d03d58d1b939e72bd100dfe531f4e6072e4c391df9e4565e598211c62->enter($__internal_2111478d03d58d1b939e72bd100dfe531f4e6072e4c391df9e4565e598211c62_prof = new Twig_Profiler_Profile($this->getTemplateName(), "template", "@Framework/Form/choice_attributes.html.php"));

        $__internal_ee96569782be16a8cbe96e97d7d6d6b07036c94b4ab51a6266d40dbbcb260023 = $this->env->getExtension("Symfony\\Bridge\\Twig\\Extension\\ProfilerExtension");
        $__internal_ee96569782be16a8cbe96e97d7d6d6b07036c94b4ab51a6266d40dbbcb260023->enter($__internal_ee96569782be16a8cbe96e97d7d6d6b07036c94b4ab51a6266d40dbbcb260023_prof = new Twig_Profiler_Profile($this->getTemplateName(), "template", "@Framework/Form/choice_attributes.html.php"));

        // line 1
        echo "<?php if (\$disabled): ?>disabled=\"disabled\" <?php endif ?>
<?php foreach (\$choice_attr as \$k => \$v): ?>
<?php if (\$v === true): ?>
<?php printf('%s=\"%s\" ', \$view->escape(\$k), \$view->escape(\$k)) ?>
<?php elseif (\$v !== false): ?>
<?php printf('%s=\"%s\" ', \$view->escape(\$k), \$view->escape(\$v)) ?>
<?php endif ?>
<?php endforeach ?>
";
        
        $__internal_2111478d03d58d1b939e72bd100dfe531f4e6072e4c391df9e4565e598211c62->leave($__internal_2111478d03d58d1b939e72bd100dfe531f4e6072e4c391df9e4565e598211c62_prof);

        
        $__internal_ee96569782be16a8cbe96e97d7d6d6b07036c94b4ab51a6266d40dbbcb260023->leave($__internal_ee96569782be16a8cbe96e97d7d6d6b07036c94b4ab51a6266d40dbbcb260023_prof);

    }

    public function getTemplateName()
    {
        return "@Framework/Form/choice_attributes.html.php";
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
        return new Twig_Source("<?php if (\$disabled): ?>disabled=\"disabled\" <?php endif ?>
<?php foreach (\$choice_attr as \$k => \$v): ?>
<?php if (\$v === true): ?>
<?php printf('%s=\"%s\" ', \$view->escape(\$k), \$view->escape(\$k)) ?>
<?php elseif (\$v !== false): ?>
<?php printf('%s=\"%s\" ', \$view->escape(\$k), \$view->escape(\$v)) ?>
<?php endif ?>
<?php endforeach ?>
", "@Framework/Form/choice_attributes.html.php", "/var/www/gradel_dev/tgsmith/test/vendor/symfony/symfony/src/Symfony/Bundle/FrameworkBundle/Resources/views/Form/choice_attributes.html.php");
    }
}
