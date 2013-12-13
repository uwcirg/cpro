<?php
echo $this->Html->css('cpro.bodydiagram');
echo $this->Html->script('classList');
$bodypart_map_js = json_encode($bodypart_map);
$symptom_map_js  = json_encode($symptom_map);
$answers_js  = json_encode($answers);
echo $this->Html->scriptBlock("
    PAIN_CLASS = 'pain';
    SEVERE_PAIN_CLASS = 'severe_pain';
    bodypart_map = $bodypart_map_js;
    symptom_map = $symptom_map_js;
    answers = $answers_js;
    PAIN_QUESTION_ID = symptom_map[PAIN_CLASS];
    SEVERE_PAIN_QUESTION_ID = symptom_map[SEVERE_PAIN_CLASS];

");
echo $this->Html->script('cpro.bodydiagram');
?>


<p>Click to indicate areas of pain</p>
<p>Click a second time to indicate a single area of severe pain</p>

<div id="pain_list">
    <h3>Pain:</h3>
    <div id="pain">
    </div>
    <h3>Severe Pain:</h3>
    <div id="severe_pain">
    </div>
</div>

<svg
   xmlns:dc="http://purl.org/dc/elements/1.1/"
   xmlns:cc="http://creativecommons.org/ns#"
   xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
   xmlns:svg="http://www.w3.org/2000/svg"
   xmlns="http://www.w3.org/2000/svg"
   xmlns:sodipodi="http://sodipodi.sourceforge.net/DTD/sodipodi-0.dtd"
   xmlns:inkscape="http://www.inkscape.org/namespaces/inkscape"
   id="svg2985"
   version="1.1"
   inkscape:version="0.48.4 r9939"
   width="263"
   height="584"
   sodipodi:docname="body_diagram_front.svg">
  <metadata
     id="metadata2991">
    <rdf:RDF>
      <cc:Work
         rdf:about="">
        <dc:format>image/svg+xml</dc:format>
        <dc:type
           rdf:resource="http://purl.org/dc/dcmitype/StillImage" />
        <dc:title />
      </cc:Work>
    </rdf:RDF>
  </metadata>
  <defs
     id="defs2989" />
  <sodipodi:namedview
     pagecolor="#ffffff"
     bordercolor="#666666"
     borderopacity="1"
     objecttolerance="10"
     gridtolerance="10"
     guidetolerance="10"
     inkscape:pageopacity="0"
     inkscape:pageshadow="2"
     inkscape:window-width="1057"
     inkscape:window-height="829"
     id="namedview2987"
     showgrid="false"
     inkscape:zoom="3.2568493"
     inkscape:cx="90.72396"
     inkscape:cy="299.85016"
     inkscape:window-x="459"
     inkscape:window-y="156"
     inkscape:window-maximized="0"
     inkscape:current-layer="svg2985" />
  <path

     d="m 91.499474,100.09674 c 0,0 -36.845426,7.98318 -33.774973,36.84543 0,0 34.114617,5.01423 57.671459,-25.49026 -15.415077,-4.45084 -23.896486,-11.35517 -23.896486,-11.35517 z"
     id="right_shoulder"

     inkscape:label="#path2997" />
  <path

     d="m 159.57861,75.736701 c -10.63857,1.302683 -16.93487,7.381868 -16.93487,7.381868 3.90804,7.164754 26.05365,17.369101 26.05365,17.369101 -44.72544,22.79694 -77.292499,-0.651342 -77.292499,-0.651342 l 19.974469,-21.060034 0.43422,-10.204347 11.50703,-8.68455 6.2963,0 -0.21711,-12.375484 c 0,0 15.8493,0 31.26438,-3.690934 l 7.81609,6.513413 c 0,0 -5.86207,6.730526 -4.34227,5.862071 1.51979,-0.868455 -1.5198,10.204347 -1.5198,10.204347 z"
     id="head_neck"

     inkscape:label="#path2999" />
  <path

     d="M 111.59647,67.486379 99.872327,52.071302 99.438099,41.866956 c 0,0 20.625811,8.467436 29.527471,5.644957 l 0.86846,11.941257 -6.2963,0.434227 z"
     id="head_back"

     inkscape:label="#path3001" />
  <path

     d="m 99.329127,41.604626 0.767613,-14.12408 c 0,0 24.10305,-35.7707674 60.64143,-5.987382 l -2.14932,5.83386 -12.2818,-5.83386 -3.3775,3.53102 1.3817,5.83386 -9.5184,9.364879 0.30704,7.215562 -6.1409,0.460568 c 0,0 -18.88328,-0.614091 -29.629863,-6.294427 z"
     id="head_top"

     inkscape:label="#path3003" />
  <path

     d="m 158.58885,27.173501 1.9958,16.580442 c 0,0 -19.49737,4.14511 -25.33123,3.684542 l -0.76762,-7.215562 9.82545,-9.364879 -1.68875,-5.680337 3.53102,-3.53102 z"
     id="face_top"

     inkscape:label="#path3005" />
  <path

     d="m 168.72135,100.55731 c 0,0 36.38485,-1.535228 34.38906,35.15667 0,0 -34.54259,5.52682 -58.95268,-25.48475 0,0 22.72135,-7.98317 24.56362,-9.67192 z"
     id="left_shoulder"

     inkscape:label="#path3007" />
  <path

     d="m 91.622004,222.5056 c 0,0 19.974466,0.86845 22.579826,-8.03321 2.60537,-8.90166 11.07281,-29.09324 17.36911,-25.1852 6.29629,3.90805 9.33589,2.38826 21.27714,28.65902 0,0 10.85569,6.94764 18.45467,4.99362 0,0 10.20435,-52.75865 7.16476,-59.05494 l 3.69093,-28.22479 c 0,0 -29.9617,-11.0728 -37.77779,-25.40231 0,0 -26.27077,1.95402 -28.65902,-0.65134 0,0 -15.849303,21.92849 -37.126451,27.13922 0,0 6.513413,49.28482 7.381868,54.71266 0.868455,5.42785 5.644957,31.04727 5.644957,31.04727 z"
     id="chest"

     inkscape:label="#path3009" />
  <path

     d="m 55.575184,137.8633 21.80021,0 7.983176,54.347 c 0,0 -12.281809,-8.59726 -27.019979,-2.7634 0,0 -0.921136,-45.74974 -2.763407,-51.5836 z"
     id="right_bicep"

     inkscape:label="#path3011" />
  <path

     d="m 58.338591,190.06099 -0.61409,6.1409 -10.132493,15.35226 c 0,0 9.211357,21.80021 33.160884,11.66772 l 2.456361,-31.9327 c 0,0 -13.817034,-7.36908 -24.870662,-1.22818 z"
     id="right_elbow"

     inkscape:label="#path3013" />
  <path

     d="m 46.670873,212.78233 c 0,0 -8.597266,50.66246 -10.746583,48.82019 l 19.650894,9.51841 25.177708,-46.05679 c 0,0 -23.028391,6.44794 -34.082019,-12.28181 z"
     id="right_forearm"

     inkscape:label="#path3015" />
  <path

     d="m 36.538381,261.90957 -8.597266,13.81703 -0.614091,9.82545 c 0,0 6.140905,11.66772 20.264984,3.07045 l 8.597266,-16.27339 z"
     id="right_wrist"

     inkscape:label="#path3017" />
  <path

     d="m 27.019979,284.93796 -12.588854,9.21136 c 0,0 -11.3606728,17.19453 -11.3606728,16.88748 0,-0.30704 3.6845425,2.76341 3.6845425,2.76341 l 10.7465823,-8.59727 1.228181,22.4143 c 0,0 0.614091,18.42272 11.360673,15.35227 10.746583,-3.07046 17.808623,-33.77498 17.808623,-33.77498 l -0.307046,-19.95794 c 0,0 -12.588853,9.21136 -20.572029,-4.29863 z"
     id="right_hand"

     inkscape:label="#path3019" />
  <path

     d="m 182.07781,134.4858 -5.52681,54.34701 3.99159,3.68454 c 0,0 9.21135,-7.06204 25.1777,-1.22818 l 0.61409,-23.33544 -4.60567,-15.6593 1.22818,-16.2734 c 0,0 -12.28182,0.30704 -20.87908,-1.53523 z"
     id="left_bicep"

     inkscape:label="#path3021" />
  <path

     d="m 206.02734,191.28917 10.13249,22.72135 c 0,0 -9.82545,21.49316 -33.77497,9.5184 l -5.21977,-16.88749 3.07045,-14.73817 c 0,0 13.81704,-7.36908 25.7918,-0.61409 z"
     id="left_elbow"

     inkscape:label="#path3023" />
  <path

     d="m 182.6919,224.45005 c 0,0 20.87908,45.13565 28.24816,48.82019 l 20.26499,-7.67613 -14.73817,-50.9695 c 0,0 -9.51841,19.95793 -33.77498,9.82544 z"
     id="left_forearm"

     inkscape:label="#path3025" />
  <path

     d="m 210.94006,273.57729 4.91273,8.59726 c 0,0 15.35226,6.44795 21.18612,-4.60568 l -5.52682,-11.66771 z"
     id="left_wrist"

     inkscape:label="#path3027" />
  <path

     d="m 215.54574,282.78864 c 0,0 1.22818,34.69611 6.755,41.75815 0,0 9.5184,14.43113 18.11566,15.96635 8.59727,1.53523 5.52682,-39.91587 5.52682,-39.91587 l 9.5184,7.06204 c 0,0 7.36908,3.37749 6.44795,-3.07046 -0.92114,-6.44795 -11.05363,-8.29022 -11.36067,-15.35226 l -13.20295,-11.05362 c 0,0 -7.67613,8.29021 -21.80021,4.60567 z"
     id="left_hand"

     inkscape:label="#path3029" />
  <path

     d="m 90.271293,223.52892 c 0,0 17.808627,-0.92114 22.107257,-5.83386 4.29863,-4.91273 11.66772,-34.69611 18.42271,-28.55521 0,0 15.04522,-3.37749 19.03681,25.48476 0,0 9.82544,12.58885 23.02839,6.75499 l 5.52681,29.78339 c 0,0 -29.47634,11.36067 -21.18612,46.36382 0,0 -16.58044,-4.91272 -24.56362,-11.05362 0,0 -17.80862,14.12408 -22.4143,9.5184 0,0 2.45636,-29.47634 -22.721344,-45.44269 0,0 -2.456362,-21.18612 2.763407,-27.01998 z"
     id="stomach"

     inkscape:label="#path3031" />
  <path

     d="m 87.507886,250.24185 c 0,0 -11.360673,19.65089 -4.605678,74.91903 0,0 20.264982,-14.73817 24.563622,-27.01998 4.29863,-12.2818 3.37749,-29.16929 -19.957944,-47.89905 z"
     id="right_waist"

     inkscape:label="#path3033" />
  <path

     d="m 181.46372,327.61724 c 0,0 -26.71293,-19.95793 -24.25657,-30.09043 2.45636,-10.13249 -11.66772,-26.09884 21.18612,-46.05678 0,0 8.59726,36.53837 3.07045,76.14721 z"
     id="left_waist"

     inkscape:label="#path3035" />
  <path

     d="m 107.46583,296.29863 c 0,0 23.94952,37.45952 49.74132,2.14932 0,0 -25.48475,-10.43954 -24.56362,-11.66772 0,0 -25.1777,12.2818 -25.1777,9.5184 z"
     id="groin"

     inkscape:label="#path3037" />
  <path

     d="m 82.288118,326.38906 c 0,0 13.509989,55.88223 16.887487,55.26814 3.377495,-0.61409 17.501575,-6.75499 31.011565,2.45636 l 3.3775,-70.31335 c 0,0 -13.50999,0 -25.48475,-16.2734 0,0 -11.360677,18.72975 -25.791802,28.86225 z"
     id="right_thigh"

     inkscape:label="#path3039" />
  <path

     d="m 168.26078,383.49947 c 0,0 -16.2734,-9.21135 -31.9327,2.45637 l -2.14932,-70.92745 c 0,0 21.49316,-9.21136 22.4143,-15.6593 0,0 7.67613,19.95793 23.94953,27.94111 0,0 0.92114,41.14405 -12.28181,56.18927 z"
     id="left_thigh"

     inkscape:label="#path3041" />
  <path

     d="m 99.48265,382.57834 0.92114,37.76656 c 0,0 3.68454,14.43113 27.32702,7.06204 l 1.84227,-43.29338 c 0,0 -16.2734,-7.98317 -30.09043,-1.53522 z"
     id="right_knee"

     inkscape:label="#path3043" />
  <path

     d="m 139.39853,426.17876 c 0,0 16.27339,9.5184 28.24816,-5.21977 l 0.92113,-36.84543 c 0,0 -18.42271,-8.59726 -31.9327,1.84228 0,0 -0.30704,37.45951 2.76341,40.22292 z"
     id="left_knee"

     inkscape:label="#path3045" />
  <path

     d="m 100.09674,421.88013 c 0,0 -12.895899,22.4143 10.43954,76.7613 l 19.95794,0.30705 c 0,0 4.29863,-71.54154 -2.45637,-70.92745 0,0 -17.19453,6.755 -27.94111,-6.1409 z"
     id="right_calve"

     inkscape:label="#path3047" />
  <path

     d="m 160.2776,498.64143 c 0,0 24.56362,-50.66246 8.59727,-77.98948 0,0 -11.36067,14.73817 -29.47634,6.1409 0,0 -11.66772,16.88749 -0.92114,70.92744 z"
     id="left_calve"

     inkscape:label="#path3049" />
  <path

     d="m 109.92219,499.56257 1.84227,27.94111 c 0,0 11.36067,5.21977 19.0368,-0.61409 l 0,-27.94111 z"
     id="right_ankle"

     inkscape:label="#path3051" />
  <path

     d="m 159.97056,499.86961 -20.26499,0 -2.7634,27.32703 c 0,0 13.20294,5.21976 21.49316,2.7634 0,0 -4.60567,-21.80021 1.53523,-30.09043 z"
     id="left_ankle"

     inkscape:label="#path3053" />
  <path

     d="m 112.37855,526.58254 c 0,0 2.14932,23.64249 -13.202945,27.01998 0,0 3.991585,17.19454 22.414305,13.50999 0,0 9.5184,-2.7634 7.98317,-38.0736 0,0 -16.88748,4.60567 -17.19453,-2.45637 z"
     id="right_foot"

     inkscape:label="#path3055" />
  <path

     d="m 136.94217,529.03891 20.87907,1.22818 c 0,0 3.68454,22.10725 14.12408,23.02839 0,0 -9.21136,16.27339 -18.42271,14.12408 0,0 -19.6509,-7.67613 -12.58886,-26.09884 l -4.60567,-4.60568 z"
     id="left_foot"

     inkscape:label="#path3057" />
</svg>
