<?php

/* @Framework/Form/form_end.html.php */
class __TwigTemplate_ba363fbd425dba3ee04e04ebf4e424d68874a07f83cd9b29648e31992d6ef3b3 extends Twig_Template
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
        $__internal_98d60d9465abfed660d97e629887c96df0ca916622df1d5d65f6b6646082f9a7 = $this->env->getExtension("Symfony\\Bundle\\WebProfilerBundle\\Twig\\WebProfilerExtension");
        $__internal_98d60d9465abfed660d97e629887c96df0ca916622df1d5d65f6b6646082f9a7->enter($__internal_98d60d9465abfed660d97e629887c96df0ca916622df1d5d65f6b6646082f9a7_prof = new Twig_Profiler_Profile($this->getTemplateName(), "template", "@Framework/Form/form_end.html.php"));

        $__internal_ea3a02a67a4c226655905279ab4708b72655c6fa476b5d189926a5a2690f049f = $this->env->getExtension("Symfony\\Bridge\\Twig\\Extension\\ProfilerExtension");
        $__internal_ea3a02a67a4c226655905279ab4708b72655c6fa476b5d189926a5a2690f049f->enter($__internal_ea3a02a67a4c226655905279ab4708b72655c6fa476b5d189926a5a2690f049f_prof = new Twig_Profiler_Profile($this->getTemplateName(), "template", "@Framework/Form/form_end.html.php"));

        // line 1
        echo "<?php if (!isset(\$render_rest) || \$render_rest): ?>
<?php echo \$view['form']->rest(\$form) ?>
<?php endif ?>
</form>
";
        
        $__internal_98d60d9465abfed660d97e629887c96df0ca916622df1d5d65f6b6646082f9a7->leave($__internal_98d60d9465abfed660d97e629887c96df0ca916622df1d5d65f6b6646082f9a7_prof);

        
        $__internal_ea3a02a67a4c226655905279ab4708b72655c6fa476b5d189926a5a2690f049f->leave($__internal_ea3a02a67a4c226655905279ab4708b72655c6fa476b5d189926a5a2690f049f_prof);

    }

    public function getTemplateName()
    {
        return "@Framework/Form/form_end.html.php";
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
        return new Twig_Source("<?php if (!isset(\$render_rest) || \$render_rest): ?>
<?php echo \$view['form']->rest(\$form) ?>
<?php endif ?>
</form>
", "@Framework/Form/form_end.html.php", "/var/www/gradel_dev/tgsmith/test/vendor/symfony/symfony/src/Symfony/Bundle/FrameworkBundle/Resources/views/Form/form_end.html.php");
    }
}
