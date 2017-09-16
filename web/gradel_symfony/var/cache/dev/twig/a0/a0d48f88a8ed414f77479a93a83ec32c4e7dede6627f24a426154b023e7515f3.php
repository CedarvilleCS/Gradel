<?php

/* @Framework/Form/button_row.html.php */
class __TwigTemplate_9eb5e1684f85d4c209494d057ce4ab24edce783152a5b30ce999200c6ca0d836 extends Twig_Template
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
        $__internal_39b3a30f124033c7315340950d3a6d40c07d68f1b81badba76b333417208bf31 = $this->env->getExtension("Symfony\\Bundle\\WebProfilerBundle\\Twig\\WebProfilerExtension");
        $__internal_39b3a30f124033c7315340950d3a6d40c07d68f1b81badba76b333417208bf31->enter($__internal_39b3a30f124033c7315340950d3a6d40c07d68f1b81badba76b333417208bf31_prof = new Twig_Profiler_Profile($this->getTemplateName(), "template", "@Framework/Form/button_row.html.php"));

        $__internal_976a1b4fc4efe9b239a9923f09dcb07d9435315b494a1801ca5227774504206b = $this->env->getExtension("Symfony\\Bridge\\Twig\\Extension\\ProfilerExtension");
        $__internal_976a1b4fc4efe9b239a9923f09dcb07d9435315b494a1801ca5227774504206b->enter($__internal_976a1b4fc4efe9b239a9923f09dcb07d9435315b494a1801ca5227774504206b_prof = new Twig_Profiler_Profile($this->getTemplateName(), "template", "@Framework/Form/button_row.html.php"));

        // line 1
        echo "<div>
    <?php echo \$view['form']->widget(\$form) ?>
</div>
";
        
        $__internal_39b3a30f124033c7315340950d3a6d40c07d68f1b81badba76b333417208bf31->leave($__internal_39b3a30f124033c7315340950d3a6d40c07d68f1b81badba76b333417208bf31_prof);

        
        $__internal_976a1b4fc4efe9b239a9923f09dcb07d9435315b494a1801ca5227774504206b->leave($__internal_976a1b4fc4efe9b239a9923f09dcb07d9435315b494a1801ca5227774504206b_prof);

    }

    public function getTemplateName()
    {
        return "@Framework/Form/button_row.html.php";
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
        return new Twig_Source("<div>
    <?php echo \$view['form']->widget(\$form) ?>
</div>
", "@Framework/Form/button_row.html.php", "/var/www/gradel_dev/tgsmith/test/vendor/symfony/symfony/src/Symfony/Bundle/FrameworkBundle/Resources/views/Form/button_row.html.php");
    }
}
