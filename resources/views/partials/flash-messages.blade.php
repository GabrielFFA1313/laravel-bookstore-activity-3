@if(session('success'))
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4">
<div

class="bg-green-100 border border-green-400 text-green-700 px-4 py-3
rounded relative" role="alert">

<span class="block sm:inline">{{ session('success') }}</

span>

</div>
</div>
@endif
@if(session('error'))
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4">
<div class="bg-red-100 border border-red-400 text-red-700 px-4

py-3 rounded relative" role="alert">

<span class="block sm:inline">{{ session('error') }}</span>
</div>
</div>
@endif
@if($errors->any())
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4">
<div class="bg-red-100 border border-red-400 text-red-700 px-4

py-3 rounded relative">

<ul class="list-disc list-inside">
@foreach(<span class="math-inline" style="display:

inline;"><math xmlns="http://www.w3.org/1998/Math/MathML"
display="inline"><mrow><mi>e</mi><mi>r</mi><mi>r</mi><mi>o</mi><mi>r</
mi><mi>s</mi><mo>&#x02212;</mo><mo>&#x0003E;</mo><mi>a</mi><mi>l</
mi><mi>l</mi><mo stretchy="false">&#x00028;</mo><mo
stretchy="false">&#x00029;</mo><mi>a</mi><mi>s</mi></mrow></math></
span>error)

<li>{{ $error }}</li>
@endforeach
</ul>
</div>
</div>
@endif