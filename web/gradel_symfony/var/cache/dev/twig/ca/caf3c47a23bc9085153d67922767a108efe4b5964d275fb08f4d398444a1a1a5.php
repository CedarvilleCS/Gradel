<?php

/* ::base.html.twig */
class __TwigTemplate_ccb7c21c8ee1c1afccef7e4a69515dde7bfb6175025858ef133f6fc569bbf07c extends Twig_Template
{
    public function __construct(Twig_Environment $env)
    {
        parent::__construct($env);

        $this->parent = false;

        $this->blocks = array(
            'title' => array($this, 'block_title'),
            'stylesheets' => array($this, 'block_stylesheets'),
            'body' => array($this, 'block_body'),
            'javascripts' => array($this, 'block_javascripts'),
        );
    }

    protected function doDisplay(array $context, array $blocks = array())
    {
        $__internal_b8487f0990a77aa956da6f9ffd8e840bd48ebc7b415f5d7efe5b46a53d92ab90 = $this->env->getExtension("Symfony\\Bundle\\WebProfilerBundle\\Twig\\WebProfilerExtension");
        $__internal_b8487f0990a77aa956da6f9ffd8e840bd48ebc7b415f5d7efe5b46a53d92ab90->enter($__internal_b8487f0990a77aa956da6f9ffd8e840bd48ebc7b415f5d7efe5b46a53d92ab90_prof = new Twig_Profiler_Profile($this->getTemplateName(), "template", "::base.html.twig"));

        $__internal_ddec6ff48deb599d6c256b421976445b5f931226a73180adedb41c359f9b96b7 = $this->env->getExtension("Symfony\\Bridge\\Twig\\Extension\\ProfilerExtension");
        $__internal_ddec6ff48deb599d6c256b421976445b5f931226a73180adedb41c359f9b96b7->enter($__internal_ddec6ff48deb599d6c256b421976445b5f931226a73180adedb41c359f9b96b7_prof = new Twig_Profiler_Profile($this->getTemplateName(), "template", "::base.html.twig"));

        // line 1
        echo "<!DOCTYPE html>
<html>
    <head>
        <meta charset=\"UTF-8\" />
        <title>";
        // line 5
        $this->displayBlock('title', $context, $blocks);
        echo "</title>
        
\t";
        // line 7
        $this->displayBlock('stylesheets', $context, $blocks);
        // line 8
        echo "\t<link rel=\"stylesheet\" href=\"";
        echo twig_escape_filter($this->env, $this->env->getExtension('Symfony\Bridge\Twig\Extension\AssetExtension')->getAssetUrl("assets/css/main.css"), "html", null, true);
        echo "\" />
\t<link rel=\"icon\" type=\"image/x-icon\" href=\"";
        // line 9
        echo twig_escape_filter($this->env, $this->env->getExtension('Symfony\Bridge\Twig\Extension\AssetExtension')->getAssetUrl("favicon.ico"), "html", null, true);
        echo "\" />
    </head>
    <body>
        ";
        // line 12
        $this->displayBlock('body', $context, $blocks);
        // line 13
        echo "        ";
        $this->displayBlock('javascripts', $context, $blocks);
        // line 14
        echo "    </body>
</html>
";
        
        $__internal_b8487f0990a77aa956da6f9ffd8e840bd48ebc7b415f5d7efe5b46a53d92ab90->leave($__internal_b8487f0990a77aa956da6f9ffd8e840bd48ebc7b415f5d7efe5b46a53d92ab90_prof);

        
        $__internal_ddec6ff48deb599d6c256b421976445b5f931226a73180adedb41c359f9b96b7->leave($__internal_ddec6ff48deb599d6c256b421976445b5f931226a73180adedb41c359f9b96b7_prof);

    }

    // line 5
    public function block_title($context, array $blocks = array())
    {
        $__internal_e11926ac43e3fce8174a4a6e51eb46749b04e40226290b1ebff74737fc9267f0 = $this->env->getExtension("Symfony\\Bundle\\WebProfilerBundle\\Twig\\WebProfilerExtension");
        $__internal_e11926ac43e3fce8174a4a6e51eb46749b04e40226290b1ebff74737fc9267f0->enter($__internal_e11926ac43e3fce8174a4a6e51eb46749b04e40226290b1ebff74737fc9267f0_prof = new Twig_Profiler_Profile($this->getTemplateName(), "block", "title"));

        $__internal_a792f6803d8577cf7e2445f29541ba549e5fc1f4c0ffa9e056d04dc8074d5954 = $this->env->getExtension("Symfony\\Bridge\\Twig\\Extension\\ProfilerExtension");
        $__internal_a792f6803d8577cf7e2445f29541ba549e5fc1f4c0ffa9e056d04dc8074d5954->enter($__internal_a792f6803d8577cf7e2445f29541ba549e5fc1f4c0ffa9e056d04dc8074d5954_prof = new Twig_Profiler_Profile($this->getTemplateName(), "block", "title"));

        echo "Welcome!";
        
        $__internal_a792f6803d8577cf7e2445f29541ba549e5fc1f4c0ffa9e056d04dc8074d5954->leave($__internal_a792f6803d8577cf7e2445f29541ba549e5fc1f4c0ffa9e056d04dc8074d5954_prof);

        
        $__internal_e11926ac43e3fce8174a4a6e51eb46749b04e40226290b1ebff74737fc9267f0->leave($__internal_e11926ac43e3fce8174a4a6e51eb46749b04e40226290b1ebff74737fc9267f0_prof);

    }

    // line 7
    public function block_stylesheets($context, array $blocks = array())
    {
        $__internal_4b3a2e60c97b954c46b2a9e89f9e5be49899f2369b3efddfb28ad23a026bb0e6 = $this->env->getExtension("Symfony\\Bundle\\WebProfilerBundle\\Twig\\WebProfilerExtension");
        $__internal_4b3a2e60c97b954c46b2a9e89f9e5be49899f2369b3efddfb28ad23a026bb0e6->enter($__internal_4b3a2e60c97b954c46b2a9e89f9e5be49899f2369b3efddfb28ad23a026bb0e6_prof = new Twig_Profiler_Profile($this->getTemplateName(), "block", "stylesheets"));

        $__internal_69e69b8d94caea2d609f0377da002bf49d5e3473be5b1638fc5120066296d947 = $this->env->getExtension("Symfony\\Bridge\\Twig\\Extension\\ProfilerExtension");
        $__internal_69e69b8d94caea2d609f0377da002bf49d5e3473be5b1638fc5120066296d947->enter($__internal_69e69b8d94caea2d609f0377da002bf49d5e3473be5b1638fc5120066296d947_prof = new Twig_Profiler_Profile($this->getTemplateName(), "block", "stylesheets"));

        echo "\t";
        
        $__internal_69e69b8d94caea2d609f0377da002bf49d5e3473be5b1638fc5120066296d947->leave($__internal_69e69b8d94caea2d609f0377da002bf49d5e3473be5b1638fc5120066296d947_prof);

        
        $__internal_4b3a2e60c97b954c46b2a9e89f9e5be49899f2369b3efddfb28ad23a026bb0e6->leave($__internal_4b3a2e60c97b954c46b2a9e89f9e5be49899f2369b3efddfb28ad23a026bb0e6_prof);

    }

    // line 12
    public function block_body($context, array $blocks = array())
    {
        $__internal_9bd01c4ec778d6c29f5a2cca391b429b651b030cfc31f9eb5b47ffdd0876b76e = $this->env->getExtension("Symfony\\Bundle\\WebProfilerBundle\\Twig\\WebProfilerExtension");
        $__internal_9bd01c4ec778d6c29f5a2cca391b429b651b030cfc31f9eb5b47ffdd0876b76e->enter($__internal_9bd01c4ec778d6c29f5a2cca391b429b651b030cfc31f9eb5b47ffdd0876b76e_prof = new Twig_Profiler_Profile($this->getTemplateName(), "block", "body"));

        $__internal_df0b83e1c105fcb48af7c4ddb55590b6c09f38e6d5c950d462e0ed68e79c86c5 = $this->env->getExtension("Symfony\\Bridge\\Twig\\Extension\\ProfilerExtension");
        $__internal_df0b83e1c105fcb48af7c4ddb55590b6c09f38e6d5c950d462e0ed68e79c86c5->enter($__internal_df0b83e1c105fcb48af7c4ddb55590b6c09f38e6d5c950d462e0ed68e79c86c5_prof = new Twig_Profiler_Profile($this->getTemplateName(), "block", "body"));

        
        $__internal_df0b83e1c105fcb48af7c4ddb55590b6c09f38e6d5c950d462e0ed68e79c86c5->leave($__internal_df0b83e1c105fcb48af7c4ddb55590b6c09f38e6d5c950d462e0ed68e79c86c5_prof);

        
        $__internal_9bd01c4ec778d6c29f5a2cca391b429b651b030cfc31f9eb5b47ffdd0876b76e->leave($__internal_9bd01c4ec778d6c29f5a2cca391b429b651b030cfc31f9eb5b47ffdd0876b76e_prof);

    }

    // line 13
    public function block_javascripts($context, array $blocks = array())
    {
        $__internal_7a2168d312f3da6536c99c2cf890f800a4ac22f3d59f248145633c16faa39a74 = $this->env->getExtension("Symfony\\Bundle\\WebProfilerBundle\\Twig\\WebProfilerExtension");
        $__internal_7a2168d312f3da6536c99c2cf890f800a4ac22f3d59f248145633c16faa39a74->enter($__internal_7a2168d312f3da6536c99c2cf890f800a4ac22f3d59f248145633c16faa39a74_prof = new Twig_Profiler_Profile($this->getTemplateName(), "block", "javascripts"));

        $__internal_c31c8b6b2490ca976b94b67860cb3b4dfdd4eae08eb7583e60f214eb9c8bc34c = $this->env->getExtension("Symfony\\Bridge\\Twig\\Extension\\ProfilerExtension");
        $__internal_c31c8b6b2490ca976b94b67860cb3b4dfdd4eae08eb7583e60f214eb9c8bc34c->enter($__internal_c31c8b6b2490ca976b94b67860cb3b4dfdd4eae08eb7583e60f214eb9c8bc34c_prof = new Twig_Profiler_Profile($this->getTemplateName(), "block", "javascripts"));

        
        $__internal_c31c8b6b2490ca976b94b67860cb3b4dfdd4eae08eb7583e60f214eb9c8bc34c->leave($__internal_c31c8b6b2490ca976b94b67860cb3b4dfdd4eae08eb7583e60f214eb9c8bc34c_prof);

        
        $__internal_7a2168d312f3da6536c99c2cf890f800a4ac22f3d59f248145633c16faa39a74->leave($__internal_7a2168d312f3da6536c99c2cf890f800a4ac22f3d59f248145633c16faa39a74_prof);

    }

    public function getTemplateName()
    {
        return "::base.html.twig";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  123 => 13,  106 => 12,  88 => 7,  70 => 5,  58 => 14,  55 => 13,  53 => 12,  47 => 9,  42 => 8,  40 => 7,  35 => 5,  29 => 1,);
    }

    /** @deprecated since 1.27 (to be removed in 2.0). Use getSourceContext() instead */
    public function getSource()
    {
        @trigger_error('The '.__METHOD__.' method is deprecated since version 1.27 and will be removed in 2.0. Use getSourceContext() instead.', E_USER_DEPRECATED);

        return $this->getSourceContext()->getCode();
    }

    public function getSourceContext()
    {
        return new Twig_Source("<!DOCTYPE html>
<html>
    <head>
        <meta charset=\"UTF-8\" />
        <title>{% block title %}Welcome!{% endblock %}</title>
        
\t{% block stylesheets %}\t{% endblock %}
\t<link rel=\"stylesheet\" href=\"{{ asset('assets/css/main.css') }}\" />
\t<link rel=\"icon\" type=\"image/x-icon\" href=\"{{ asset('favicon.ico') }}\" />
    </head>
    <body>
        {% block body %}{% endblock %}
        {% block javascripts %}{% endblock %}
    </body>
</html>
", "::base.html.twig", "/var/www/gradel_dev/tgsmith/test/app/Resources/views/base.html.twig");
    }
}
