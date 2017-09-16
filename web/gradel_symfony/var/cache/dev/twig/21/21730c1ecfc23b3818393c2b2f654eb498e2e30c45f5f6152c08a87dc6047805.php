<?php

/* @Framework/Form/reset_widget.html.php */
class __TwigTemplate_c9bc84a19fabbd8e27d99cb8208e01dbbdb7715dad259eb49b3fa1b46e5148d5 extends Twig_Template
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
        $__internal_8d9531a5267574a08931b6fa3fc074470e0869e61ea0e694d3cae43ba7a013de = $this->env->getExtension("Symfony\\Bundle\\WebProfilerBundle\\Twig\\WebProfilerExtension");
        $__internal_8d9531a5267574a08931b6fa3fc074470e0869e61ea0e694d3cae43ba7a013de->enter($__internal_8d9531a5267574a08931b6fa3fc074470e0869e61ea0e694d3cae43ba7a013de_prof = new Twig_Profiler_Profile($this->getTemplateName(), "template", "@Framework/Form/reset_widget.html.php"));

        $__internal_6027a27ab188cf49259e774a807ffb8e96c400a3be897a0fa39a843d92fc95bb = $this->env->getExtension("Symfony\\Bridge\\Twig\\Extension\\ProfilerExtension");
        $__internal_6027a27ab188cf49259e774a807ffb8e96c400a3be897a0fa39a843d92fc95bb->enter($__internal_6027a27ab188cf49259e774a807ffb8e96c400a3be897a0fa39a843d92fc95bb_prof = new Twig_Profiler_Profile($this->getTemplateName(), "template", "@Framework/Form/reset_widget.html.php"));

        // line 1
        echo "<?php echo \$view['form']->block(\$form, 'button_widget', array('type' => isset(\$type) ? \$type : 'reset')) ?>
";
        
        $__internal_8d9531a5267574a08931b6fa3fc074470e0869e61ea0e694d3cae43ba7a013de->leave($__internal_8d9531a5267574a08931b6fa3fc074470e0869e61ea0e694d3cae43ba7a013de_prof);

        
        $__internal_6027a27ab188cf49259e774a807ffb8e96c400a3be897a0fa39a843d92fc95bb->leave($__internal_6027a27ab188cf49259e774a807ffb8e96c400a3be897a0fa39a843d92fc95bb_prof);

    }

    public function getTemplateName()
    {
        return "@Framework/Form/reset_widget.html.php";
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
        return new Twig_Source("<?php echo \$view['form']->block(\$form, 'button_widget', array('type' => isset(\$type) ? \$type : 'reset')) ?>
", "@Framework/Form/reset_widget.html.php", "/var/www/gradel_dev/tgsmith/test/vendor/symfony/symfony/src/Symfony/Bundle/FrameworkBundle/Resources/views/Form/reset_widget.html.php");
    }
}
