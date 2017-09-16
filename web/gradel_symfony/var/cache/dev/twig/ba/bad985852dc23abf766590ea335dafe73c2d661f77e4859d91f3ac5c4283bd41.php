<?php

/* TwigBundle:Exception:error.atom.twig */
class __TwigTemplate_af7a231c65a32d7171b9e3d870102ae46696bc2e331bd4aff7856a9f5ca24cda extends Twig_Template
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
        $__internal_04905775c25664f313014b5eb3d0844662b79ffa886f198f404719e5426acc06 = $this->env->getExtension("Symfony\\Bundle\\WebProfilerBundle\\Twig\\WebProfilerExtension");
        $__internal_04905775c25664f313014b5eb3d0844662b79ffa886f198f404719e5426acc06->enter($__internal_04905775c25664f313014b5eb3d0844662b79ffa886f198f404719e5426acc06_prof = new Twig_Profiler_Profile($this->getTemplateName(), "template", "TwigBundle:Exception:error.atom.twig"));

        $__internal_837600c07635ac327b4c40790cf4181558389a70fe1ea32d778f50ec1b2dedee = $this->env->getExtension("Symfony\\Bridge\\Twig\\Extension\\ProfilerExtension");
        $__internal_837600c07635ac327b4c40790cf4181558389a70fe1ea32d778f50ec1b2dedee->enter($__internal_837600c07635ac327b4c40790cf4181558389a70fe1ea32d778f50ec1b2dedee_prof = new Twig_Profiler_Profile($this->getTemplateName(), "template", "TwigBundle:Exception:error.atom.twig"));

        // line 1
        echo twig_include($this->env, $context, "@Twig/Exception/error.xml.twig");
        echo "
";
        
        $__internal_04905775c25664f313014b5eb3d0844662b79ffa886f198f404719e5426acc06->leave($__internal_04905775c25664f313014b5eb3d0844662b79ffa886f198f404719e5426acc06_prof);

        
        $__internal_837600c07635ac327b4c40790cf4181558389a70fe1ea32d778f50ec1b2dedee->leave($__internal_837600c07635ac327b4c40790cf4181558389a70fe1ea32d778f50ec1b2dedee_prof);

    }

    public function getTemplateName()
    {
        return "TwigBundle:Exception:error.atom.twig";
    }

    public function isTraitable()
    {
        return false;
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
        return new Twig_Source("{{ include('@Twig/Exception/error.xml.twig') }}
", "TwigBundle:Exception:error.atom.twig", "/var/www/gradel_dev/tgsmith/test/vendor/symfony/symfony/src/Symfony/Bundle/TwigBundle/Resources/views/Exception/error.atom.twig");
    }
}
