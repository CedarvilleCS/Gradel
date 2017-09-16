<?php

/* @Twig/layout.html.twig */
class __TwigTemplate_fc9abcb20a4afe17de75a55851aa04d226fae91a9ecf8de642b08493f42edfa9 extends Twig_Template
{
    public function __construct(Twig_Environment $env)
    {
        parent::__construct($env);

        $this->parent = false;

        $this->blocks = array(
            'title' => array($this, 'block_title'),
            'head' => array($this, 'block_head'),
            'body' => array($this, 'block_body'),
        );
    }

    protected function doDisplay(array $context, array $blocks = array())
    {
        $__internal_a908ed9e808a27b6c6e1cad2312466b70000e0576ffbc900abc2ce1c1c370c17 = $this->env->getExtension("Symfony\\Bundle\\WebProfilerBundle\\Twig\\WebProfilerExtension");
        $__internal_a908ed9e808a27b6c6e1cad2312466b70000e0576ffbc900abc2ce1c1c370c17->enter($__internal_a908ed9e808a27b6c6e1cad2312466b70000e0576ffbc900abc2ce1c1c370c17_prof = new Twig_Profiler_Profile($this->getTemplateName(), "template", "@Twig/layout.html.twig"));

        $__internal_c767d46c8d9d3f9bc97fd4d5cf67d0c8278030f74f293a21e0b655ef63d5a164 = $this->env->getExtension("Symfony\\Bridge\\Twig\\Extension\\ProfilerExtension");
        $__internal_c767d46c8d9d3f9bc97fd4d5cf67d0c8278030f74f293a21e0b655ef63d5a164->enter($__internal_c767d46c8d9d3f9bc97fd4d5cf67d0c8278030f74f293a21e0b655ef63d5a164_prof = new Twig_Profiler_Profile($this->getTemplateName(), "template", "@Twig/layout.html.twig"));

        // line 1
        echo "<!DOCTYPE html>
<html>
    <head>
        <meta charset=\"";
        // line 4
        echo twig_escape_filter($this->env, $this->env->getCharset(), "html", null, true);
        echo "\" />
        <meta name=\"robots\" content=\"noindex,nofollow\" />
        <meta name=\"viewport\" content=\"width=device-width,initial-scale=1\" />
        <title>";
        // line 7
        $this->displayBlock('title', $context, $blocks);
        echo "</title>
        <link rel=\"icon\" type=\"image/png\" href=\"";
        // line 8
        echo twig_include($this->env, $context, "@Twig/images/favicon.png.base64");
        echo "\">
        <style>";
        // line 9
        echo twig_include($this->env, $context, "@Twig/exception.css.twig");
        echo "</style>
        ";
        // line 10
        $this->displayBlock('head', $context, $blocks);
        // line 11
        echo "    </head>
    <body>
        <header>
            <div class=\"container\">
                <h1 class=\"logo\">";
        // line 15
        echo twig_include($this->env, $context, "@Twig/images/symfony-logo.svg");
        echo " Symfony Exception</h1>

                <div class=\"help-link\">
                    <a href=\"https://symfony.com/doc\">
                        <span class=\"icon\">";
        // line 19
        echo twig_include($this->env, $context, "@Twig/images/icon-book.svg");
        echo "</span>
                        <span class=\"hidden-xs-down\">Symfony</span> Docs
                    </a>
                </div>

                <div class=\"help-link\">
                    <a href=\"https://symfony.com/support\">
                        <span class=\"icon\">";
        // line 26
        echo twig_include($this->env, $context, "@Twig/images/icon-support.svg");
        echo "</span>
                        <span class=\"hidden-xs-down\">Symfony</span> Support
                    </a>
                </div>
            </div>
        </header>

        ";
        // line 33
        $this->displayBlock('body', $context, $blocks);
        // line 34
        echo "        ";
        echo twig_include($this->env, $context, "@Twig/base_js.html.twig");
        echo "
    </body>
</html>
";
        
        $__internal_a908ed9e808a27b6c6e1cad2312466b70000e0576ffbc900abc2ce1c1c370c17->leave($__internal_a908ed9e808a27b6c6e1cad2312466b70000e0576ffbc900abc2ce1c1c370c17_prof);

        
        $__internal_c767d46c8d9d3f9bc97fd4d5cf67d0c8278030f74f293a21e0b655ef63d5a164->leave($__internal_c767d46c8d9d3f9bc97fd4d5cf67d0c8278030f74f293a21e0b655ef63d5a164_prof);

    }

    // line 7
    public function block_title($context, array $blocks = array())
    {
        $__internal_102167eda476cf6fc8d2bcd23752799258b64f7a2a382a5d992072c7257ce298 = $this->env->getExtension("Symfony\\Bundle\\WebProfilerBundle\\Twig\\WebProfilerExtension");
        $__internal_102167eda476cf6fc8d2bcd23752799258b64f7a2a382a5d992072c7257ce298->enter($__internal_102167eda476cf6fc8d2bcd23752799258b64f7a2a382a5d992072c7257ce298_prof = new Twig_Profiler_Profile($this->getTemplateName(), "block", "title"));

        $__internal_a9e91a9fb2c9204643eded7396a1b82233e3bec94275efd44f165ae85e251b4e = $this->env->getExtension("Symfony\\Bridge\\Twig\\Extension\\ProfilerExtension");
        $__internal_a9e91a9fb2c9204643eded7396a1b82233e3bec94275efd44f165ae85e251b4e->enter($__internal_a9e91a9fb2c9204643eded7396a1b82233e3bec94275efd44f165ae85e251b4e_prof = new Twig_Profiler_Profile($this->getTemplateName(), "block", "title"));

        
        $__internal_a9e91a9fb2c9204643eded7396a1b82233e3bec94275efd44f165ae85e251b4e->leave($__internal_a9e91a9fb2c9204643eded7396a1b82233e3bec94275efd44f165ae85e251b4e_prof);

        
        $__internal_102167eda476cf6fc8d2bcd23752799258b64f7a2a382a5d992072c7257ce298->leave($__internal_102167eda476cf6fc8d2bcd23752799258b64f7a2a382a5d992072c7257ce298_prof);

    }

    // line 10
    public function block_head($context, array $blocks = array())
    {
        $__internal_c031b1ebecea6855085b1bdce2e9c6f8163b564728728c77b56667eee42ef8e8 = $this->env->getExtension("Symfony\\Bundle\\WebProfilerBundle\\Twig\\WebProfilerExtension");
        $__internal_c031b1ebecea6855085b1bdce2e9c6f8163b564728728c77b56667eee42ef8e8->enter($__internal_c031b1ebecea6855085b1bdce2e9c6f8163b564728728c77b56667eee42ef8e8_prof = new Twig_Profiler_Profile($this->getTemplateName(), "block", "head"));

        $__internal_a27b5e74a23aa1152a97fa69b26cc57e6058a4428c8cd42007ceb8d4e7a25769 = $this->env->getExtension("Symfony\\Bridge\\Twig\\Extension\\ProfilerExtension");
        $__internal_a27b5e74a23aa1152a97fa69b26cc57e6058a4428c8cd42007ceb8d4e7a25769->enter($__internal_a27b5e74a23aa1152a97fa69b26cc57e6058a4428c8cd42007ceb8d4e7a25769_prof = new Twig_Profiler_Profile($this->getTemplateName(), "block", "head"));

        
        $__internal_a27b5e74a23aa1152a97fa69b26cc57e6058a4428c8cd42007ceb8d4e7a25769->leave($__internal_a27b5e74a23aa1152a97fa69b26cc57e6058a4428c8cd42007ceb8d4e7a25769_prof);

        
        $__internal_c031b1ebecea6855085b1bdce2e9c6f8163b564728728c77b56667eee42ef8e8->leave($__internal_c031b1ebecea6855085b1bdce2e9c6f8163b564728728c77b56667eee42ef8e8_prof);

    }

    // line 33
    public function block_body($context, array $blocks = array())
    {
        $__internal_5d0f948a07a541bea098ec905095ca7b02e9eb550180174979dd2f04e8702d9b = $this->env->getExtension("Symfony\\Bundle\\WebProfilerBundle\\Twig\\WebProfilerExtension");
        $__internal_5d0f948a07a541bea098ec905095ca7b02e9eb550180174979dd2f04e8702d9b->enter($__internal_5d0f948a07a541bea098ec905095ca7b02e9eb550180174979dd2f04e8702d9b_prof = new Twig_Profiler_Profile($this->getTemplateName(), "block", "body"));

        $__internal_a6ca676d8c8f4a1e4e0103a0bb58ae6214272376d72d91bfdfc443ce6b2b937d = $this->env->getExtension("Symfony\\Bridge\\Twig\\Extension\\ProfilerExtension");
        $__internal_a6ca676d8c8f4a1e4e0103a0bb58ae6214272376d72d91bfdfc443ce6b2b937d->enter($__internal_a6ca676d8c8f4a1e4e0103a0bb58ae6214272376d72d91bfdfc443ce6b2b937d_prof = new Twig_Profiler_Profile($this->getTemplateName(), "block", "body"));

        
        $__internal_a6ca676d8c8f4a1e4e0103a0bb58ae6214272376d72d91bfdfc443ce6b2b937d->leave($__internal_a6ca676d8c8f4a1e4e0103a0bb58ae6214272376d72d91bfdfc443ce6b2b937d_prof);

        
        $__internal_5d0f948a07a541bea098ec905095ca7b02e9eb550180174979dd2f04e8702d9b->leave($__internal_5d0f948a07a541bea098ec905095ca7b02e9eb550180174979dd2f04e8702d9b_prof);

    }

    public function getTemplateName()
    {
        return "@Twig/layout.html.twig";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  137 => 33,  120 => 10,  103 => 7,  88 => 34,  86 => 33,  76 => 26,  66 => 19,  59 => 15,  53 => 11,  51 => 10,  47 => 9,  43 => 8,  39 => 7,  33 => 4,  28 => 1,);
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
        <meta charset=\"{{ _charset }}\" />
        <meta name=\"robots\" content=\"noindex,nofollow\" />
        <meta name=\"viewport\" content=\"width=device-width,initial-scale=1\" />
        <title>{% block title %}{% endblock %}</title>
        <link rel=\"icon\" type=\"image/png\" href=\"{{ include('@Twig/images/favicon.png.base64') }}\">
        <style>{{ include('@Twig/exception.css.twig') }}</style>
        {% block head %}{% endblock %}
    </head>
    <body>
        <header>
            <div class=\"container\">
                <h1 class=\"logo\">{{ include('@Twig/images/symfony-logo.svg') }} Symfony Exception</h1>

                <div class=\"help-link\">
                    <a href=\"https://symfony.com/doc\">
                        <span class=\"icon\">{{ include('@Twig/images/icon-book.svg') }}</span>
                        <span class=\"hidden-xs-down\">Symfony</span> Docs
                    </a>
                </div>

                <div class=\"help-link\">
                    <a href=\"https://symfony.com/support\">
                        <span class=\"icon\">{{ include('@Twig/images/icon-support.svg') }}</span>
                        <span class=\"hidden-xs-down\">Symfony</span> Support
                    </a>
                </div>
            </div>
        </header>

        {% block body %}{% endblock %}
        {{ include('@Twig/base_js.html.twig') }}
    </body>
</html>
", "@Twig/layout.html.twig", "/var/www/gradel_dev/tgsmith/test/vendor/symfony/symfony/src/Symfony/Bundle/TwigBundle/Resources/views/layout.html.twig");
    }
}
