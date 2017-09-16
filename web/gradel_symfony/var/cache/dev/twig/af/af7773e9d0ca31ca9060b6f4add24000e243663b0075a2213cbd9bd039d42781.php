<?php

/* @Framework/FormTable/form_row.html.php */
class __TwigTemplate_edc9aaef04fa8619329105cd4b97f06c797c8f553c4df2118495d67fd5d7ed8a extends Twig_Template
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
        $__internal_6e86584c23a851217b275839fc3fb6782365e716af7d25cc1fbfbd429bd0b205 = $this->env->getExtension("Symfony\\Bundle\\WebProfilerBundle\\Twig\\WebProfilerExtension");
        $__internal_6e86584c23a851217b275839fc3fb6782365e716af7d25cc1fbfbd429bd0b205->enter($__internal_6e86584c23a851217b275839fc3fb6782365e716af7d25cc1fbfbd429bd0b205_prof = new Twig_Profiler_Profile($this->getTemplateName(), "template", "@Framework/FormTable/form_row.html.php"));

        $__internal_11959dd33220f9738474e5d8f606c3c8ff6f20290503a69778b5de4839afde29 = $this->env->getExtension("Symfony\\Bridge\\Twig\\Extension\\ProfilerExtension");
        $__internal_11959dd33220f9738474e5d8f606c3c8ff6f20290503a69778b5de4839afde29->enter($__internal_11959dd33220f9738474e5d8f606c3c8ff6f20290503a69778b5de4839afde29_prof = new Twig_Profiler_Profile($this->getTemplateName(), "template", "@Framework/FormTable/form_row.html.php"));

        // line 1
        echo "<tr>
    <td>
        <?php echo \$view['form']->label(\$form) ?>
    </td>
    <td>
        <?php echo \$view['form']->errors(\$form) ?>
        <?php echo \$view['form']->widget(\$form) ?>
    </td>
</tr>
";
        
        $__internal_6e86584c23a851217b275839fc3fb6782365e716af7d25cc1fbfbd429bd0b205->leave($__internal_6e86584c23a851217b275839fc3fb6782365e716af7d25cc1fbfbd429bd0b205_prof);

        
        $__internal_11959dd33220f9738474e5d8f606c3c8ff6f20290503a69778b5de4839afde29->leave($__internal_11959dd33220f9738474e5d8f606c3c8ff6f20290503a69778b5de4839afde29_prof);

    }

    public function getTemplateName()
    {
        return "@Framework/FormTable/form_row.html.php";
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
    <td>
        <?php echo \$view['form']->label(\$form) ?>
    </td>
    <td>
        <?php echo \$view['form']->errors(\$form) ?>
        <?php echo \$view['form']->widget(\$form) ?>
    </td>
</tr>
", "@Framework/FormTable/form_row.html.php", "/var/www/gradel_dev/tgsmith/test/vendor/symfony/symfony/src/Symfony/Bundle/FrameworkBundle/Resources/views/FormTable/form_row.html.php");
    }
}
