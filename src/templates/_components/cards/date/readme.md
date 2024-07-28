Tags: {{ tags|join(',') }}

Hello

<h1>{{ title }}: {{ handle }}</h1>

<pre><code>
&#123;&#37; include '{{ handle }}' with {{ context|json_encode(constant("JSON_PRETTY_PRINT")) }} &#37;&#125;
</code></pre>
