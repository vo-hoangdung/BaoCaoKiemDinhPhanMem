package vn.edu.classroom.Support;

import java.util.regex.Matcher;
import java.util.regex.Pattern;

public final class RoomHtml {
    private RoomHtml() {
    }

    public static String roomIdByName(String html, String roomName) {
        String quotedName = Pattern.quote(roomName);
        Pattern pattern = Pattern.compile("data-room-id=\"(\\d+)\"[^>]*data-room-name=\"" + quotedName + "\"");
        Matcher matcher = pattern.matcher(html);
        if (!matcher.find()) {
            throw new AssertionError("Không tìm thấy phòng trong dashboard: " + roomName);
        }
        return matcher.group(1);
    }
}
