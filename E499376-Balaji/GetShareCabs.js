// Pseudo code for available share cabs
//function avaialblesharecabs()
//{
//    var lat = geoLocationService.MostRecentPosition.Latitude;
//    var lon = geoLocationService.MostRecentPosition.Longitude;

//    List<cGeogatePoint> l1 = new List<cGeogatePoint>
//    {
//        new cGeogatePoint (stop1.GpsCoordinate.Latitude , stop1.GpsCoordinate.Longitude),
//        new cGeogatePoint (stop1.GpsCoordinate2.Latitude, stop1.GpsCoordinate2.Longitude),
//        new cGeogatePoint (stop1.GpsCoordinate3.Latitude , stop1.GpsCoordinate3.Longitude),
//        new cGeogatePoint (stop1.GpsCoordinate4.Latitude , stop1.GpsCoordinate4.Longitude)
//    };

//    cGeoGate geocord = new cGeoGate(l1);
//    bool reacheddest = geocord.Contains(lat, lon);
//    if(reacheddest)
//    {

//        TimerForGettingGeoLocation.StopTimer();
//    }
//}
// 
//public class cGeogatePoint
//{
//    public double lat;
//    public double lon;

//    public cGeogatePoint(double dlat, double dlon)
//        {
//            lat = dlat;
//            lon = dlon;
//        }
//    }