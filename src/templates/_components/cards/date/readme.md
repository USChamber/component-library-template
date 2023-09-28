Tags: {{ tags|join(',') }}

<h1>{{ title }}: {{ handle }}</h1>

<pre><code>
&#123;&#37; include '{{ handle }}' with {{ jsonPrettyPrint(context) }} &#37;&#125;
</code></pre>
